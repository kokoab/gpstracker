# 🚀 Development Workflow with Live Arduino Updates

## 🎯 Goal:
Make changes locally and see them immediately with real Arduino data!

---

## 🌐 How It Works:

```
[Your PC - localhost:8000]
         ↓
[LocalTunnel - Creates public URL]
         ↓
    (Internet)
         ↑
[Arduino + SIM900A] ← Sends GPS data via cellular
```

---

## ⚡ QUICK START:

### Step 1: Start Development Tunnel
```bash
cd ~/documents/gps/laravel

# Start tunnel (keeps running)
./start_dev_tunnel.sh
```

**You'll see something like:**
```
your url is: https://gps-tracker-angelo.loca.lt
```

### Step 2: Update Arduino with Tunnel URL
```cpp
// In arduino_gps_tracker.ino, change line 23:
const char* SERVER_URL = "https://gps-tracker-angelo.loca.lt";

// Upload to Arduino
```

### Step 3: Make Changes & See Them Live!
```bash
# In another terminal:
nano resources/views/map.blade.php

# Save changes
# Arduino automatically shows them!
# View at: http://localhost:8000
```

---

## 📋 DETAILED WORKFLOW:

### Terminal 1: Docker Containers
```bash
cd ~/documents/gps/laravel
docker-compose up -d
```

### Terminal 2: Development Tunnel
```bash
cd ~/documents/gps/laravel
./start_dev_tunnel.sh
```

**Copy the tunnel URL** (e.g., `https://gps-tracker-angelo.loca.lt`)

### Terminal 3: Development Work
```bash
# Make your changes
nano resources/views/map.blade.php

# Changes are live immediately!
# No need to restart anything
```

### Arduino IDE:
```cpp
// Update SERVER_URL with your tunnel URL
const char* SERVER_URL = "https://gps-tracker-angelo.loca.lt";

// Upload to Arduino
// Open Serial Monitor
// Watch data flow to your localhost!
```

---

## 🔄 Development Cycle:

```
1. Start tunnel → Get URL
2. Update Arduino → Upload code
3. Make changes → Edit files
4. See results → Check browser (localhost:8000)
5. Repeat 3-4 as needed
6. Done? Deploy to EC2
```

---

## 🎨 Example: Adding a New Feature

```bash
# 1. Start tunnel (Terminal 1)
./start_dev_tunnel.sh
# URL: https://gps-tracker-angelo.loca.lt

# 2. Update Arduino and upload
const char* SERVER_URL = "https://gps-tracker-angelo.loca.lt";

# 3. Make changes (Terminal 2)
nano resources/views/map.blade.php
# Add new feature...
# Save file

# 4. View immediately
# Arduino sends → Tunnel → localhost:8000
# Open browser: http://localhost:8000
# See your changes with REAL GPS data!

# 5. Keep iterating
# Edit → Save → Refresh browser → See changes
# No deploy needed!
```

---

## 🌟 ADVANTAGES:

✅ **No curl commands** - Real Arduino data  
✅ **Instant changes** - Edit and see immediately  
✅ **Real GPS** - Actual movement tracked  
✅ **Local speed** - No deployment delays  
✅ **Easy debugging** - All logs on your PC  

---

## ⚠️ IMPORTANT NOTES:

### **About LocalTunnel URLs:**
- URL changes each time you restart the tunnel
- Free forever (no cost)
- Might be slower than EC2 (internet → your PC)
- Perfect for development!

### **When to Use What:**

| Scenario | URL to Use | Where |
|----------|------------|-------|
| **Development** | Tunnel URL | Arduino → Your PC |
| **View Results** | `localhost:8000` | Your browser |
| **Production** | EC2 IP | Arduino → EC2 |

---

## 🚀 DEPLOYMENT (When Done):

```bash
# 1. Stop the tunnel (Ctrl+C in tunnel terminal)

# 2. Update Arduino with EC2 URL
const char* SERVER_URL = "http://54.254.135.119";

# 3. Deploy to EC2
cd ~/documents/gps
./deploy_to_ec2.sh

# 4. Upload Arduino with EC2 URL

# Done! Now it's live on EC2
```

---

## 🐛 TROUBLESHOOTING:

### Tunnel won't start
```bash
# Install/reinstall localtunnel
npm install -g localtunnel

# Or use ngrok instead:
ngrok http 8000
```

### Arduino timeout
```bash
# Check tunnel is running
# Check Arduino has tunnel URL
# Try HTTPS instead of HTTP (localtunnel prefers HTTPS)
```

### Can't access localhost:8000
```bash
# Check Docker is running
docker-compose ps

# Restart if needed
docker-compose restart app
```

---

## 💡 PRO TIPS:

### Tip 1: Keep Tunnel Running
Leave the tunnel terminal open while developing. Only restart if you need a new URL.

### Tip 2: Use HTTPS for LocalTunnel
```cpp
// LocalTunnel provides HTTPS:
const char* SERVER_URL = "https://gps-tracker-angelo.loca.lt";
```

### Tip 3: Monitor in Real-Time
```bash
# Watch Laravel logs while developing
docker logs -f laravel_app
```

### Tip 4: Quick Restart
```bash
# If something breaks:
docker-compose restart app
# Keep tunnel running!
```

---

## 📊 COMPLETE SETUP:

```bash
# Terminal 1: Docker
cd ~/documents/gps/laravel
docker-compose up -d

# Terminal 2: Tunnel
cd ~/documents/gps/laravel
./start_dev_tunnel.sh
# Copy URL: https://gps-tracker-angelo.loca.lt

# Terminal 3: Logs (optional)
docker logs -f laravel_app

# Terminal 4: Development
cd ~/documents/gps/laravel
# Edit files here

# Arduino IDE:
# Update SERVER_URL with tunnel URL
# Upload and watch!

# Browser:
# Open http://localhost:8000
# See live updates!
```

---

## ✅ YOU'RE ALL SET!

Now you can:
- Make changes locally ✅
- See them with real Arduino data ✅
- No manual curl commands ✅
- Instant feedback ✅
- Deploy to EC2 when ready ✅

**Happy developing! 🎉**

