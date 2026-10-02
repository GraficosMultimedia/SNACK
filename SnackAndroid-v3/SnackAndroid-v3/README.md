# SNACK Android v3

Proyecto Android limpio para SNACK POS con Bluetooth ESC/POS.

## Qué corrige esta versión

- `settings.gradle.kts` completo con `pluginManagement` y repositorios.
- Versiones de Android Gradle Plugin y Kotlin declaradas en el proyecto raíz.
- `gradle-wrapper.properties` incluido.
- Java/Kotlin fijados a JVM 17.
- `compileSdk`/`targetSdk` 35.
- Memoria de Gradle y Kotlin daemon configurada para evitar cierres por memoria demasiado agresivos.
- Manifest con permisos Bluetooth modernos para Android 12+.
- `MainActivity`, `BluetoothPrinter` y `EscPosImage` incluidos.

## Abrir en Android Studio

1. Descomprime el ZIP.
2. Abre **la carpeta raíz `SnackAndroid-v3`**, no la carpeta `app`.
3. Espera a que Android Studio reconozca `settings.gradle.kts`.
4. Si aparece **Sync Now**, pulsa ese botón.
5. En Settings > Build, Execution, Deployment > Build Tools > Gradle, usa **Gradle JVM 17**.
6. Ejecuta `app` en el teléfono.
7. Vincula la impresora térmica desde los ajustes Bluetooth del teléfono.
8. Concede los permisos Bluetooth cuando la aplicación los solicite.

## Importante

El proyecto no necesita una dependencia externa de AndroidX para la pantalla principal. La lógica de impresión usa Bluetooth RFCOMM con el UUID estándar SPP y comandos ESC/POS.
