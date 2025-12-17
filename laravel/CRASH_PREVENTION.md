# 🛡️ Crash Prevention & Auto-Restart Guide

## ✅ What's Already Implemented:

### 1. **Docker Auto-Restart Policy**
- ✅ `restart: unless-stopped` in docker-compose.yaml
- ✅ Containers automatically restart if they crash
- ✅ Works even after system reboot

### 2. **Resource Limits**
- ✅ Memory limits prevent OOM (Out of Memory) kills
- ✅ App container: 512MB limit (reserves 256MB)
- ✅ DB container: 384MB limit (reserves 128MB)
- ✅ Total: ~896MB (leaves ~128MB for system)

### 3. **Health Checks**
- ✅ Docker monitors container health
- ✅ Automatically restarts unhealthy containers
- ✅ Checks every 30 seconds

### 4. **Watchdog Script** (Optional - Extra Safety)
- ✅ Monitors containers every minute
- ✅ Restarts if down
- ✅ Checks app responsiveness
- ✅ Monitors system resources
- ✅ Logs all actions

---

## 🚀 Setup Instructions:

### **Step 1: Deploy Updated docker-compose.yaml**

```bash
# On your local machine:
cd ~/documents/gps/laravel

# Commit the changes
git add docker-compose.yaml
git commit -m "feat: Add resource limits and health checks"
git push origin feature/trail-speed-notifications

# Deploy to EC2
cd ..
./deploy_branch_to_ec2.sh feature/trail-speed-notifications
```

### **Step 2: Setup Watchdog on EC2** (Optional but Recommended)

```bash
# SSH into EC2
ssh -i ~/documents/gps/laravel/gps-tracker.pem ubuntu@54.254.135.119

# Navigate to project
cd ~/gpstracker/laravel

# Setup watchdog
chmod +x setup_watchdog.sh
./setup_watchdog.sh

# Test it
./docker_watchdog.sh

# View logs
tail -f watchdog.log
```

---

## 📊 How It Works:

### **Layer 1: Docker Restart Policy**
```
Container crashes
    ↓
Docker detects it
    ↓
Automatically restarts (unless-stopped)
```

### **Layer 2: Resource Limits**
```
Memory usage grows
    ↓
Hits 512MB limit
    ↓
Container throttled (not killed)
    ↓
Prevents OOM crash
```

### **Layer 3: Health Checks**
```
Container running but unhealthy
    ↓
Health check fails 3 times
    ↓
Docker restarts container
```

### **Layer 4: Watchdog (Extra Safety)**
```
Every minute:
    ↓
Check if containers running
    ↓
If not → Restart
    ↓
Check if app responding
    ↓
If not → Restart
    ↓
Log everything
```

---

## 🔍 Monitoring:

### **Check Container Status:**
```bash
docker ps
docker stats  # Real-time resource usage
```

### **Check Health:**
```bash
docker ps --format "table {{.Names}}\t{{.Status}}"
# Should show "healthy" or "starting"
```

### **View Watchdog Logs:**
```bash
tail -f ~/gpstracker/laravel/watchdog.log
```

### **Check System Resources:**
```bash
free -h      # Memory
df -h        # Disk
docker stats # Container resources
```

---

## 🐛 Troubleshooting:

### **Container Keeps Restarting:**
```bash
# Check logs to see why
docker logs laravel_app --tail 100

# Common causes:
# - Code error
# - Missing .env variables
# - Database connection failed
# - Out of memory (check limits)
```

### **Watchdog Not Working:**
```bash
# Check if cron is running
crontab -l

# Test watchdog manually
./docker_watchdog.sh

# Check permissions
chmod +x docker_watchdog.sh
```

### **High Memory Usage:**
```bash
# Check what's using memory
docker stats

# Clean up Docker
docker system prune -a

# Restart containers
docker-compose restart
```

---

## 📈 Resource Limits Explained:

### **Why These Limits?**

**t2.micro has 1GB RAM total:**
- System: ~200MB
- Docker overhead: ~100MB
- App container: 512MB (limit)
- DB container: 384MB (limit)
- **Total: ~1.2GB** (slightly over, but limits prevent crashes)

**If memory is tight:**
- Docker will throttle containers
- They'll run slower but won't crash
- System stays stable

---

## 🎯 Best Practices:

### **1. Regular Monitoring**
```bash
# Set up daily check
crontab -e
# Add: 0 0 * * * docker system prune -f
```

### **2. Log Rotation**
```bash
# Watchdog log auto-rotates (keeps last 1000 lines)
# Docker logs: Configure in docker-compose.yaml
```

### **3. Backup Before Changes**
```bash
# Before deploying new code:
docker-compose exec db mysqldump -u root -proot gps_tracker > backup.sql
```

### **4. Test Locally First**
```bash
# Always test changes locally before deploying
docker-compose up -d
# Test...
# Then deploy to EC2
```

---

## ✅ Verification Checklist:

After setup, verify:

- [ ] `docker ps` shows both containers running
- [ ] `docker ps` shows "healthy" or "starting" status
- [ ] `docker stats` shows memory within limits
- [ ] `curl http://localhost:8000` works
- [ ] Watchdog log exists and has entries
- [ ] `crontab -l` shows watchdog entry

---

## 🚨 Emergency Recovery:

If everything crashes:

```bash
# 1. Connect via EC2 Instance Connect (AWS Console)

# 2. Check what happened
docker ps -a
docker logs laravel_app --tail 100

# 3. Clean restart
cd ~/gpstracker/laravel
docker-compose down
docker system prune -f
docker-compose up -d

# 4. Verify
docker ps
curl http://localhost:8000
```

---

## 📝 Summary:

**With all these protections:**
- ✅ Containers auto-restart if they crash
- ✅ Memory limits prevent OOM kills
- ✅ Health checks catch issues early
- ✅ Watchdog provides extra safety
- ✅ System stays stable

**Your GPS tracker should now be much more reliable!** 🎉

