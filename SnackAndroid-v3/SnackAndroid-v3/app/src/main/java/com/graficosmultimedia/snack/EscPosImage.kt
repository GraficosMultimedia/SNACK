package com.graficosmultimedia.snack

import android.graphics.Bitmap
import java.io.ByteArrayOutputStream

object EscPosImage {
    fun raster(bitmap: Bitmap, maxWidth: Int = 384): ByteArray {
        val source = if (bitmap.width > maxWidth) {
            val ratio = maxWidth.toFloat() / bitmap.width.toFloat()
            Bitmap.createScaledBitmap(bitmap, maxWidth, (bitmap.height * ratio).toInt().coerceAtLeast(1), true)
        } else bitmap

        val width = source.width
        val height = source.height
        val widthBytes = (width + 7) / 8
        val out = ByteArrayOutputStream()

        out.write(byteArrayOf(0x1D, 0x76, 0x30, 0x00,
            (widthBytes and 0xFF).toByte(), ((widthBytes shr 8) and 0xFF).toByte(),
            (height and 0xFF).toByte(), ((height shr 8) and 0xFF).toByte()))

        for (y in 0 until height) {
            for (xb in 0 until widthBytes) {
                var value = 0
                for (bit in 0..7) {
                    val x = xb * 8 + bit
                    if (x < width) {
                        val pixel = source.getPixel(x, y)
                        val r = (pixel shr 16) and 0xFF
                        val g = (pixel shr 8) and 0xFF
                        val b = pixel and 0xFF
                        val gray = (r * 299 + g * 587 + b * 114) / 1000
                        if (gray < 160) value = value or (1 shl (7 - bit))
                    }
                }
                out.write(value)
            }
        }

        if (source !== bitmap) source.recycle()
        return out.toByteArray()
    }
}
