package com.graficosmultimedia.snack

import android.Manifest
import android.annotation.SuppressLint
import android.app.Activity
import android.bluetooth.BluetoothAdapter
import android.bluetooth.BluetoothDevice
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Typeface
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.view.Gravity
import android.view.ViewGroup
import android.widget.Button
import android.widget.LinearLayout
import android.widget.ScrollView
import android.widget.TextView
import android.widget.Toast
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

class MainActivity : Activity() {
    private val bluetoothPrinter = BluetoothPrinter()
    private val permissionRequestCode = 1001
    private var selectedDevice: BluetoothDevice? = null
    private lateinit var status: TextView
    private lateinit var devicesBox: LinearLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        buildUi()
        requestBluetoothPermissionsIfNeeded()
    }

    private fun buildUi() {
        val root = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(dp(20), dp(18), dp(20), dp(20))
        }

        val title = TextView(this).apply {
            text = "SNACK POS v3"
            textSize = 28f
            typeface = Typeface.DEFAULT_BOLD
        }
        root.addView(title, lp())

        val subtitle = TextView(this).apply {
            text = "Bluetooth · Impresora térmica ESC/POS"
            textSize = 15f
        }
        root.addView(subtitle, lp())

        status = TextView(this).apply {
            text = "Estado: esperando Bluetooth..."
            textSize = 16f
            setPadding(0, dp(18), 0, dp(12))
        }
        root.addView(status, lp())

        val refresh = Button(this).apply {
            text = "Actualizar impresoras vinculadas"
            setOnClickListener { loadPairedDevices() }
        }
        root.addView(refresh, lp())

        devicesBox = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
        }
        root.addView(devicesBox, lp())

        val print = Button(this).apply {
            text = "IMPRIMIR TICKET DE PRUEBA"
            setOnClickListener { printTestTicket() }
        }
        root.addView(print, lp())

        val settings = Button(this).apply {
            text = "Abrir ajustes de Bluetooth"
            setOnClickListener {
                try { startActivity(Intent(Settings.ACTION_BLUETOOTH_SETTINGS)) } catch (_: Exception) { }
            }
        }
        root.addView(settings, lp())

        val scroll = ScrollView(this)
        scroll.addView(root)
        setContentView(scroll)
    }

    private fun lp(): LinearLayout.LayoutParams =
        LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT).apply {
            bottomMargin = dp(8)
        }

    private fun dp(value: Int): Int = (value * resources.displayMetrics.density).toInt()

    private fun requestBluetoothPermissionsIfNeeded() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            val permissions = arrayOf(
                Manifest.permission.BLUETOOTH_CONNECT,
                Manifest.permission.BLUETOOTH_SCAN
            )
            val missing = permissions.filter {
                checkSelfPermission(it) != PackageManager.PERMISSION_GRANTED
            }
            if (missing.isNotEmpty()) {
                requestPermissions(missing.toTypedArray(), permissionRequestCode)
                return
            }
        }
        loadPairedDevices()
    }

    override fun onRequestPermissionsResult(requestCode: Int, permissions: Array<out String>, grantResults: IntArray) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        if (requestCode == permissionRequestCode) {
            if (grantResults.isNotEmpty() && grantResults.all { it == PackageManager.PERMISSION_GRANTED }) {
                loadPairedDevices()
            } else {
                status.text = "Estado: permisos Bluetooth no concedidos."
            }
        }
    }

    @SuppressLint("MissingPermission")
    private fun loadPairedDevices() {
        val adapter = BluetoothAdapter.getDefaultAdapter()
        if (adapter == null) {
            status.text = "Estado: este equipo no tiene Bluetooth."
            return
        }
        if (!adapter.isEnabled) {
            status.text = "Estado: Bluetooth está apagado."
            return
        }

        devicesBox.removeAllViews()
        val devices = adapter.bondedDevices.sortedBy { it.name ?: it.address }
        if (devices.isEmpty()) {
            status.text = "Estado: no hay impresoras vinculadas."
            return
        }

        status.text = "Estado: ${devices.size} dispositivo(s) vinculado(s)."
        devices.forEach { device ->
            val button = Button(this).apply {
                text = "${device.name ?: "Sin nombre"}\n${device.address}"
                gravity = Gravity.START or Gravity.CENTER_VERTICAL
                setOnClickListener {
                    selectedDevice = device
                    status.text = "Seleccionada: ${device.name ?: device.address}"
                    Toast.makeText(this@MainActivity, "Impresora seleccionada", Toast.LENGTH_SHORT).show()
                }
            }
            devicesBox.addView(button, lp())
        }
    }

    private fun printTestTicket() {
        val device = selectedDevice
        if (device == null) {
            Toast.makeText(this, "Selecciona una impresora primero.", Toast.LENGTH_SHORT).show()
            return
        }

        status.text = "Estado: conectando..."
        Thread {
            try {
                bluetoothPrinter.connect(device)
                val now = SimpleDateFormat("dd/MM/yyyy HH:mm:ss", Locale.getDefault()).format(Date())
                val data = buildString {
                    append("\u001B@")
                    append("\u001Ba\u0001")
                    append("SNACK POS\n")
                    append("VERSION 3.0.0\n")
                    append("\u001Ba\u0000")
                    append("--------------------------------\n")
                    append("TICKET DE PRUEBA\n")
                    append("Fecha: $now\n")
                    append("Impresora: ${device.name ?: device.address}\n")
                    append("--------------------------------\n")
                    append("Bluetooth ESC/POS OK\n")
                    append("Gradle / Kotlin / Android OK\n")
                    append("--------------------------------\n")
                    append("\n\n\n")
                    append("\u001DV\u0001")
                }
                bluetoothPrinter.printText(data)
                bluetoothPrinter.close()
                runOnUiThread { status.text = "Estado: ticket impreso correctamente." }
            } catch (e: Exception) {
                bluetoothPrinter.close()
                runOnUiThread {
                    status.text = "Error de impresión: ${e.message ?: "desconocido"}"
                    Toast.makeText(this, "No se pudo imprimir", Toast.LENGTH_LONG).show()
                }
            }
        }.start()
    }

    override fun onDestroy() {
        bluetoothPrinter.close()
        super.onDestroy()
    }
}
