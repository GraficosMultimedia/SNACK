The Gradle wrapper JAR is not bundled because this build environment has no network access to download it.
Android Studio can import the project using gradle-wrapper.properties and download Gradle 8.10.2 when network access is available.
If you need command-line ./gradlew, run `gradle wrapper` once from a machine with Gradle installed, or let Android Studio regenerate the wrapper.
