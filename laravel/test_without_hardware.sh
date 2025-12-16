#!/bin/bash

# GPS Tracker - Test Without Hardware
# This script simulates the Arduino sending GPS data

echo "🧪 Testing GPS Tracker System..."
echo ""

# Test 1: EDSA Ayala
echo "📍 Sending Location 1: EDSA Ayala, Makati"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5649,"longitude":121.0250}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

sleep 2

# Test 2: SM Mall of Asia
echo ""
echo "📍 Sending Location 2: SM Mall of Asia"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5359,"longitude":120.9823}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

sleep 2

# Test 3: Intramuros
echo ""
echo "📍 Sending Location 3: Intramuros, Manila"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5920,"longitude":120.9750}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

sleep 2

# Test 4: BGC
echo ""
echo "📍 Sending Location 4: BGC, Taguig"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5547,"longitude":121.0467}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Test Complete!"
echo ""
echo "Check latest location:"
curl -s http://localhost:8000/api/location/latest | grep -o '"latitude":[^,]*,"longitude":[^,]*' | sed 's/"//g'
echo ""
echo "Open map: http://localhost:8000"
echo "The marker should now be at BGC! 📍"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"


# GPS Tracker - Test Without Hardware
# This script simulates the Arduino sending GPS data

echo "🧪 Testing GPS Tracker System..."
echo ""

# Test 1: EDSA Ayala
echo "📍 Sending Location 1: EDSA Ayala, Makati"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5649,"longitude":121.0250}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

sleep 2

# Test 2: SM Mall of Asia
echo ""
echo "📍 Sending Location 2: SM Mall of Asia"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5359,"longitude":120.9823}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

sleep 2

# Test 3: Intramuros
echo ""
echo "📍 Sending Location 3: Intramuros, Manila"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5920,"longitude":120.9750}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

sleep 2

# Test 4: BGC
echo ""
echo "📍 Sending Location 4: BGC, Taguig"
curl -X POST http://localhost:8000/api/location \
  -H "Content-Type: application/json" \
  -d '{"device_id":"BUS001","latitude":14.5547,"longitude":121.0467}' \
  2>&1 | grep -q "success" && echo "✓ Sent successfully" || echo "✗ Failed"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Test Complete!"
echo ""
echo "Check latest location:"
curl -s http://localhost:8000/api/location/latest | grep -o '"latitude":[^,]*,"longitude":[^,]*' | sed 's/"//g'
echo ""
echo "Open map: http://localhost:8000"
echo "The marker should now be at BGC! 📍"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

