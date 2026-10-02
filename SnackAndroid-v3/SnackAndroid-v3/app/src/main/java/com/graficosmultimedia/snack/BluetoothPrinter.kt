package com.graficosmultimedia.snack

import android.annotation.SuppressLint
import android.bluetooth.BluetoothAdapter
import android.bluetooth.BluetoothDevice
import android.bluetooth.BluetoothSocket
import java.io.IOException
import java.io.OutputStream
import java.util.UUID

class BluetoothPrinter {
    companion object {
        private val SPP_UUID: UUID = UUID.fromString("00001101-0000-1000-8000-00805F9B34FB")
    }

    private var socket: BluetoothSocket? = null
    private var output: OutputStream? = null

    @SuppressLint("MissingPermission")
    @Throws(IOException::class)
    fun connect(device: BluetoothDevice) {
        close()
        val adapter = BluetoothAdapter.getDefaultAdapter()
            ?: throw IOException("El dispositivo no tiene Bluetooth.")

        adapter.cancelDiscovery()
        socket = device.createRfcommSocketToServiceRecord(SPP_UUID)
        socket!!.connect()
        output = socket!!.outputStream
    }

    @Throws(IOException::class)
    fun print(bytes: ByteArray) {
        val stream = output ?: throw IOException("La impresora no está conectada.")
        stream.write(bytes)
        stream.flush()
    }

    @Throws(IOException::class)
    fun printText(text: String) {
        print(text.toByteArray(Charsets.UTF_8))
    }

    fun close() {
        try { output?.close() } catch (_: Exception) { }
        try { socket?.close() } catch (_: Exception) { }
        output = null
        socket = null
    }
}
