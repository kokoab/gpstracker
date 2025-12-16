/*
 * GPS TRACKER - SIM900A + NEO-6M + Arduino UNO
 * Sends GPS coordinates via HTTP POST to Laravel backend
 *
 * CONNECTIONS (IMPORTANT - RX/TX MUST BE CORRECT!):
 * SIM900A: TX->Pin7, RX->Pin8, GND->GND, VCC->External 5V/2A
 * NEO-6M:  TX->Pin5, RX->Pin4, GND->GND, VCC->5V
 *
 * POWER: SIM900A needs external 5V/2A power supply!
 *
 * APN SETTINGS (Philippines):
 * Globe: internet.globe.com.ph
 * Smart: smartlte
 */

#include <SoftwareSerial.h>

SoftwareSerial gsm(7, 8);  // GSM RX, TX
SoftwareSerial gps(4, 5);  // GPS RX, TX

// Configuration
const char* DEVICE_ID = "BUS001";
const char* SERVER_URL = "http://873269cbf4d930d08f7ce607a7ef1d4f.serveousercontent.com";  // Serveo tunnel (HTTP works!)
const char* APN = "internet.globe.com.ph";  // TM uses Globe network (same APN)
const int UPDATE_INTERVAL = 10000;  // Send every 10 seconds (10000ms)

// GPS data
String latitude = "";
String longitude = "";
bool gpsFixed = false;
unsigned long lastSendTime = 0;

void setup() {
  Serial.begin(9600);

  // Initialize both serial ports but GPS listens first
  gps.begin(9600);
  gps.listen();  // GPS active by default

  gsm.begin(9600);  // GSM initialized but not listening

  delay(2000);

  Serial.println("=== GPS TRACKER STARTING ===");
  Serial.println("Device: " + String(DEVICE_ID));
  Serial.println("Server: " + String(SERVER_URL));
  Serial.println("APN: " + String(APN));
  Serial.println("Update Interval: " + String(UPDATE_INTERVAL/1000) + "s");
  Serial.println("============================");

  // Test SIM900A communication
  Serial.println("\nTesting SIM900A...");
  gsm.listen();  // Switch to GSM for test
  delay(100);
  gsm.println("AT");
  delay(1000);
  if (gsm.available()) {
    Serial.println("✅ SIM900A OK:");
    while (gsm.available()) {
      Serial.write(gsm.read());
    }
  } else {
    Serial.println("❌ NO RESPONSE - Check wiring! TX↔RX might be swapped!");
  }
  Serial.println();

  // Initialize GSM
  initGSM();

  // Return to GPS listening
  gps.listen();
  Serial.println("✓ Switched back to GPS");
}

void loop() {
  // Read GPS continuously (GPS is listening by default)
  readGPS();

  // Send location if GPS is fixed and interval elapsed
  if (gpsFixed && (millis() - lastSendTime >= UPDATE_INTERVAL)) {
    sendLocationHTTP();
    lastSendTime = millis();
  }
}

// ============ GPS FUNCTIONS ============

void readGPS() {
  gps.listen();  // Ensure GPS is active

  while (gps.available()) {
    String line = gps.readStringUntil('\n');
    line.trim();
    if (line.startsWith("$GPRMC")) {
      parseGPRMC(line);
    }
  }
}

void parseGPRMC(String data) {
  String values[12];
  int lastComma = -1;

  for (int i = 0; i < 12; i++) {
    int commaIndex = data.indexOf(',', lastComma + 1);
    if (commaIndex == -1) break;
    values[i] = data.substring(lastComma + 1, commaIndex);
    lastComma = commaIndex;
  }

  // Check if GPS has valid fix (A = active, V = void)
  if (values[2] == "A") {
    latitude = convertToDecimal(values[3], values[4], true);   // true = latitude (2 digits)
    longitude = convertToDecimal(values[5], values[6], false); // false = longitude (3 digits)

    if (latitude != "" && longitude != "") {
      if (!gpsFixed) {
        Serial.println("✓ GPS FIXED!");
        Serial.println("Lat: " + latitude + ", Lon: " + longitude);
      }
      gpsFixed = true;
    }
  } else {
    gpsFixed = false;
  }
}

String convertToDecimal(String coord, String dir, bool isLatitude) {
  if (coord == "") return "";

  // NMEA format:
  // Latitude:  DDMM.MMMM (2 digits for degrees)
  // Longitude: DDDMM.MMMM (3 digits for degrees)
  int degLen = isLatitude ? 2 : 3;

  double deg = coord.substring(0, degLen).toDouble();
  double min = coord.substring(degLen).toDouble();
  double decimal = deg + (min / 60.0);

  // Apply direction (S/W are negative)
  if (dir == "S" || dir == "W") decimal *= -1;

  return String(decimal, 6);
}

// ============ GSM/HTTP FUNCTIONS ============

void initGSM() {
  Serial.println("\n--- Initializing GSM ---");

  // Switch to GSM
  gsm.listen();
  delay(500);

  sendAT("AT", 1000);
  sendAT("AT+CPIN?", 1000);  // Check SIM

  // Setup GPRS
  Serial.println("Setting up GPRS...");
  sendAT("AT+SAPBR=3,1,\"CONTYPE\",\"GPRS\"", 2000);

  gsm.print("AT+SAPBR=3,1,\"APN\",\"");
  gsm.print(APN);
  gsm.println("\"");
  delay(2000);

  sendAT("AT+SAPBR=1,1", 3000);  // Open GPRS
  sendAT("AT+SAPBR=2,1", 2000);  // Query GPRS (should show IP)

  Serial.println("✓ GSM Ready");
}

void sendLocationHTTP() {
  Serial.println("\n--- Sending Location ---");
  Serial.println("Lat: " + latitude);
  Serial.println("Lon: " + longitude);

  // 🚨 STOP GPS - Switch to GSM
  gps.end();
  delay(500);

  gsm.listen();  // GSM takes control
  delay(500);

  // Prepare JSON payload
  String json = "{\"device_id\":\"" + String(DEVICE_ID) +
                "\",\"latitude\":" + latitude +
                ",\"longitude\":" + longitude + "}";

  Serial.println("JSON: " + json);

  // Terminate any existing HTTP session first (prevents ERROR)
  gsm.println("AT+HTTPTERM");
  delay(500);
  while (gsm.available()) gsm.read();  // Clear response

  // Initialize HTTP
  sendAT("AT+HTTPINIT", 2000);
  sendAT("AT+HTTPPARA=\"CID\",1", 1000);

  // Set URL
  gsm.print("AT+HTTPPARA=\"URL\",\"");
  gsm.print(SERVER_URL);
  gsm.println("/api/location\"");
  delay(1000);

  // Set content type
  sendAT("AT+HTTPPARA=\"CONTENT\",\"application/json\"", 1000);

  // Prepare to send data
  gsm.print("AT+HTTPDATA=");
  gsm.print(json.length());
  gsm.println(",10000");
  delay(2000);

  // Send JSON data
  gsm.println(json);
  delay(3000);

  // Execute POST request
  Serial.println("Executing POST...");
  gsm.println("AT+HTTPACTION=1");  // 1 = POST
  delay(1000);

  // Wait for +HTTPACTION response (can take 5-15 seconds)
  Serial.println("Waiting for response...");
  unsigned long startWait = millis();
  bool gotResponse = false;

  while (millis() - startWait < 20000) {  // Wait up to 20 seconds
    if (gsm.available()) {
      char c = gsm.read();
      Serial.write(c);

      // Check for success indicator
      static String buffer = "";
      buffer += c;
      if (buffer.indexOf("+HTTPACTION:") >= 0) {
        gotResponse = true;
        // Keep reading the rest of the line
        delay(100);
        while (gsm.available()) {
          Serial.write(gsm.read());
        }
        break;
      }
      if (buffer.length() > 100) buffer = "";  // Reset buffer
    }
  }

  if (!gotResponse) {
    Serial.println("\n⚠ No +HTTPACTION response (timeout)");
  }

  // Read server response
  delay(1000);
  sendAT("AT+HTTPREAD", 2000);

  // Terminate HTTP
  sendAT("AT+HTTPTERM", 1000);

  Serial.println("✓ Location sent");

  // 🔄 RESTART GPS
  gps.begin(9600);
  gps.listen();
  Serial.println("✓ GPS resumed");
}

void sendAT(const char* cmd, unsigned long waitTime) {
  gsm.println(cmd);
  delay(waitTime);

  // Print response for debugging
  while (gsm.available()) {
    Serial.write(gsm.read());
  }
}

// ============ SMS FUNCTIONS (BACKUP) ============

void sendSMS(String number, String message) {
  Serial.println("Sending SMS to " + number);

  sendAT("AT", 500);
  sendAT("AT+CMGF=1", 500);

  gsm.print("AT+CMGS=\"");
  gsm.print(number);
  gsm.println("\"");
  delay(500);

  gsm.print(message);
  gsm.write(26);  // Ctrl+Z
  delay(5000);

  Serial.println("SMS sent");
}

void sendGPSviaSMS(String number) {
  if (gpsFixed) {
    String msg = "GPS: Lat=" + latitude + ", Lon=" + longitude;
    sendSMS(number, msg);
  } else {
    Serial.println("GPS not fixed, cannot send SMS");
  }
}
