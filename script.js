// Essential JavaScript for Website Components
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Website loaded - Initializing components...');
    
    // Set minimum date for appointment booking (today)
    const today = new Date().toISOString().split('T')[0];
    const preferredDateInput = document.getElementById('preferred-date');
    const birthDateInput = document.getElementById('birth-date');
    
    if (preferredDateInput) {
        preferredDateInput.setAttribute('min', today);
    }
    
    if (birthDateInput) {
        birthDateInput.setAttribute('max', today);
    }
    
    // Initialize components with delay to ensure DOM is ready
    setTimeout(() => {
        initializeAppointmentForm();
        initializeChatbot();
        initializeMapSearch();
        initializeLanguageSystem();
        initializeScrollToTop();
    }, 500);
});

// Appointment Form Handling
function initializeAppointmentForm() {
    const appointmentForm = document.getElementById('appointment-form');
    if (!appointmentForm) return;
    
    console.log('📅 Initializing appointment form...');
    
    // Form validation
    function validateForm() {
        const requiredFields = appointmentForm.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });
        
        // Validate email format
        const emailField = document.getElementById('email-apt');
        if (emailField && emailField.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                emailField.classList.add('border-red-500');
                isValid = false;
            }
        }
        
        // Check reCAPTCHA
        if (typeof grecaptcha !== 'undefined') {
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                showMessage('Please complete the reCAPTCHA verification.', 'error');
                isValid = false;
            }
        }
        
        // Check terms agreement
        const termsCheckbox = document.getElementById('terms-agreement');
        if (termsCheckbox && !termsCheckbox.checked) {
            showMessage('Please agree to the Terms and Conditions.', 'error');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showMessage(message, type) {
        const messageDiv = document.getElementById('appointment-form-message');
        if (!messageDiv) return;
        
        messageDiv.classList.remove('hidden');
        
        if (type === 'success') {
            messageDiv.className = 'p-4 rounded-md text-center bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800';
        } else {
            messageDiv.className = 'p-4 rounded-md text-center bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800';
        }
        
        messageDiv.textContent = message;
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, type === 'success' ? 5000 : 8000);
    }
    
    // Form submission
    appointmentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('📋 Appointment form submitted');
        
        if (!validateForm()) {
            showMessage('Please fill in all required fields correctly.', 'error');
            return;
        }
        
        const formData = new FormData(appointmentForm);
        const submitBtn = appointmentForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
        submitBtn.disabled = true;
        
        // Simulate form submission
        setTimeout(() => {
            // Reset reCAPTCHA
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
            }
            
            showMessage(
                'Your appointment request has been submitted successfully! We will contact you within 24 hours to confirm your appointment details.',
                'success'
            );
            
            appointmentForm.reset();
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            document.getElementById('appointment').scrollIntoView({ behavior: 'smooth' });
        }, 3000);
    });
    
    // Real-time validation
    const inputs = appointmentForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('border-red-500') && this.value.trim()) {
                this.classList.remove('border-red-500');
            }
        });
    });
}

// Global variables for map functionality
let globalMap = null;
let globalMarker = null;
let searchMarker = null;

// Map Search Functionality
function initializeMapSearch() {
    console.log('🔍 Initializing map search...');
    
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const clearBtn = document.getElementById('clear-btn');
    
    if (!searchInput || !searchBtn || !clearBtn) {
        console.warn('⚠️ Map search elements not found');
        return;
    }
    
    // Wait for map to be available and get map instance
    function waitForMap() {
        return new Promise((resolve) => {
            const checkMap = () => {
                // Check if window.map exists (created by the HTML script)
                if (window.map && typeof window.map.setView === 'function') {
                    globalMap = window.map;
                    console.log('✅ Found window.map instance');
                    resolve(globalMap);
                } else if (typeof L !== 'undefined') {
                    // Try to find the map in Leaflet's registry
                    const mapElement = document.getElementById('map');
                    if (mapElement && mapElement._leaflet_id) {
                        // Try to get map from Leaflet's internal registry
                        const leafletId = mapElement._leaflet_id;
                        if (L.Util && L.Util.stamp && window[L.Util.stamp(mapElement)]) {
                            globalMap = window[L.Util.stamp(mapElement)];
                            console.log('✅ Found map in Leaflet registry');
                            resolve(globalMap);
                        } else {
                            // Try alternative method to get map instance
                            for (let key in window) {
                                if (window[key] && typeof window[key].setView === 'function' && window[key]._container === mapElement) {
                                    globalMap = window[key];
                                    console.log('✅ Found map instance via window search');
                                    resolve(globalMap);
                                    return;
                                }
                            }
                            setTimeout(checkMap, 300);
                        }
                    } else {
                        setTimeout(checkMap, 300);
                    }
                } else {
                    console.warn('⚠️ Leaflet not loaded yet, waiting...');
                    setTimeout(checkMap, 500);
                }
            };
            checkMap();
        });
    }

    // Initialize map interaction features
    function initializeMapInteractions() {
        if (!globalMap) return;
        
        console.log('🗺️ Adding map interaction features...');
        
        // Add click handler for map exploration
        globalMap.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            console.log('Map clicked at:', lat, lng);
            
            // Remove previous click marker if exists
            if (window.clickMarker) {
                globalMap.removeLayer(window.clickMarker);
            }
            
            // Add marker at clicked location
            window.clickMarker = L.marker([lat, lng])
                .addTo(globalMap)
                .bindPopup(`
                    <div class="p-2">
                        <h3 class="font-bold" style="color: #4a90e2;">Location</h3>
                        <p class="text-sm mt-1">Latitude: ${lat.toFixed(6)}</p>
                        <p class="text-sm">Longitude: ${lng.toFixed(6)}</p>
                        <button onclick="searchNearbyLocation(${lat}, ${lng})" class="mt-2 bg-primary text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                            Search This Area
                        </button>
                    </div>
                `)
                .openPopup();
        });
        
        // Add double-click handler for zooming
        globalMap.on('dblclick', function(e) {
            globalMap.setView(e.latlng, Math.min(globalMap.getZoom() + 2, 18));
        });
        
        console.log('✅ Map interactions initialized');
    }

    // Global function for searching nearby locations
    window.searchNearbyLocation = async function(lat, lng) {
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
                headers: {
                    'User-Agent': 'HealthSanitationWebsite/1.0'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data && data.display_name) {
                    showSearchMessage(`Location: ${data.display_name}`, 'success');
                    
                    // Update the popup with location info
                    if (window.clickMarker) {
                        window.clickMarker.setPopupContent(`
                            <div class="p-2">
                                <h3 class="font-bold" style="color: #4a90e2;">Found Location</h3>
                                <p class="text-sm mt-1">${data.display_name}</p>
                                <p class="text-xs text-gray-600 mt-1">Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</p>
                            </div>
                        `);
                    }
                } else {
                    showSearchMessage('No location information found for this area.', 'error');
                }
            }
        } catch (error) {
            console.error('Reverse geocoding error:', error);
            showSearchMessage('Unable to get location information.', 'error');
        }
    };
    
    // Search function
    async function performSearch() {
        const query = searchInput.value.trim();
        if (!query) {
            showSearchMessage('Please enter a location to search.', 'error');
            return;
        }
        
        console.log('🔍 Searching for:', query);
        showSearchMessage('Searching...', 'info');
        
        try {
            // Wait for map to be ready
            await waitForMap();
            
            // Use Nominatim API for geocoding
            const searchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=ph`;
            
            const response = await fetch(searchUrl);
            const data = await response.json();
            
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lon = parseFloat(result.lon);
                
                // Update map view
                if (globalMap) {
                    globalMap.setView([lat, lon], 16);
                    
                    // Remove existing search marker
                    if (searchMarker) {
                        globalMap.removeLayer(searchMarker);
                    }
                    
                    // Add new search marker
                    searchMarker = L.marker([lat, lon])
                        .addTo(globalMap)
                        .bindPopup(`
                            <div class="p-2">
                                <h3 class="font-bold" style="color: #4a90e2;">Search Result</h3>
                                <p class="text-sm mt-1">${result.display_name}</p>
                            </div>
                        `)
                        .openPopup();
                }
                
                showSearchMessage(`Found: ${result.display_name}`, 'success');
            } else {
                showSearchMessage('Location not found. Please try a different search term.', 'error');
            }
        } catch (error) {
            console.error('❌ Search error:', error);
            showSearchMessage('Search failed. Please check your internet connection and try again.', 'error');
        }
    }
    
    // Clear function
    async function clearSearch() {
        searchInput.value = '';
        
        try {
            // Wait for map to be ready
            await waitForMap();
            
            // Remove search marker if it exists
            if (searchMarker && globalMap) {
                globalMap.removeLayer(searchMarker);
                searchMarker = null;
            }
            
            // Reset map to default location (Caloocan coordinates)
            if (globalMap) {
                const defaultLocation = [14.6507, 120.9676];
                globalMap.setView(defaultLocation, 13);
            }
            
            // Clear any search messages
            const messageDiv = document.getElementById('search-message');
            if (messageDiv) {
                messageDiv.classList.add('hidden');
            }
            
            console.log('🧹 Search cleared');
        } catch (error) {
            console.error('❌ Clear error:', error);
        }
    }
    
    // Enhanced search function with better error handling
    async function performSearchEnhanced() {
        const query = searchInput.value.trim();
        if (!query) {
            showSearchMessage('Please enter a location to search.', 'error');
            return;
        }
        
        console.log('🔍 Searching for:', query);
        showSearchMessage('Searching...', 'info');
        
        try {
            // Wait for map to be ready
            await waitForMap();
            
            // Initialize map interactions if not done yet
            if (globalMap && !globalMap._interactionsInitialized) {
                initializeMapInteractions();
                globalMap._interactionsInitialized = true;
            }
            
            // Try multiple geocoding services with better error handling
            let result = null;
            
            // Define multiple search strategies
            const searchStrategies = [
                // Strategy 1: Direct Nominatim with Philippines focus
                {
                    name: 'Nominatim Philippines',
                    url: `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ' Philippines')}&limit=5&countrycodes=ph&addressdetails=1`,
                    headers: {
                        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept': 'application/json',
                        'Referer': window.location.origin
                    }
                },
                // Strategy 2: Alternative Nominatim endpoint
                {
                    name: 'Nominatim Alternative',
                    url: `https://nominatim.openstreetmap.org/search.php?q=${encodeURIComponent(query)}&format=json&polygon=1&addressdetails=1&limit=5`,
                    headers: {
                        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept': 'application/json'
                    }
                },
                // Strategy 3: Photon geocoder (alternative service)
                {
                    name: 'Photon Geocoder',
                    url: `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5`,
                    headers: {
                        'Accept': 'application/json'
                    }
                },
                // Strategy 4: Simple Nominatim without extra parameters
                {
                    name: 'Simple Nominatim',
                    url: `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=3`,
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            ];
            
            // Try each strategy
            for (let i = 0; i < searchStrategies.length && !result; i++) {
                const strategy = searchStrategies[i];
                try {
                    console.log(`🔍 Trying ${strategy.name}:`, strategy.url);
                    showSearchMessage(`Trying ${strategy.name}...`, 'info');
                    
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
                    
                    const response = await fetch(strategy.url, {
                        headers: strategy.headers,
                        signal: controller.signal,
                        mode: 'cors'
                    });
                    
                    clearTimeout(timeoutId);
                    
                    if (response.ok) {
                        const data = await response.json();
                        console.log(`✅ ${strategy.name} results:`, data);
                        
                        if (data && Array.isArray(data) && data.length > 0) {
                            // Handle different response formats
                            if (strategy.name === 'Photon Geocoder') {
                                // Photon has different structure
                                const feature = data.find(f => f.geometry && f.properties);
                                if (feature) {
                                    result = {
                                        lat: feature.geometry.coordinates[1],
                                        lon: feature.geometry.coordinates[0],
                                        display_name: feature.properties.name || feature.properties.label || query
                                    };
                                }
                            } else {
                                // Standard Nominatim format
                                result = data.find(item => 
                                    item.display_name && item.lat && item.lon &&
                                    (item.display_name.toLowerCase().includes('philippines') || 
                                     item.display_name.toLowerCase().includes('manila') ||
                                     item.importance > 0.3)
                                ) || data[0];
                            }
                            
                            if (result) {
                                console.log(`✅ Found result with ${strategy.name}:`, result);
                                break;
                            }
                        }
                    } else {
                        console.warn(`❌ ${strategy.name} failed with status:`, response.status, response.statusText);
                    }
                } catch (searchError) {
                    if (searchError.name === 'AbortError') {
                        console.warn(`⏱️ ${strategy.name} timed out`);
                    } else {
                        console.warn(`❌ ${strategy.name} error:`, searchError.message);
                    }
                }
                
                // Add delay between attempts (except for last attempt)
                if (!result && i < searchStrategies.length - 1) {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
            }
            
            if (result && result.lat && result.lon) {
                const lat = parseFloat(result.lat);
                const lon = parseFloat(result.lon);
                
                console.log('Found coordinates:', lat, lon);
                
                if (isNaN(lat) || isNaN(lon)) {
                    throw new Error('Invalid coordinates received');
                }
                
                // Update map view
                if (globalMap && typeof globalMap.setView === 'function') {
                    console.log('Setting map view to:', lat, lon);
                    globalMap.setView([lat, lon], 15);
                    
                    // Remove existing search marker
                    if (searchMarker) {
                        globalMap.removeLayer(searchMarker);
                    }
                    
                    // Add new search marker with enhanced popup
                    searchMarker = L.marker([lat, lon], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        })
                    })
                        .addTo(globalMap)
                        .bindPopup(`
                            <div class="p-3">
                                <h3 class="font-bold text-lg" style="color: #4a90e2;">🔍 Search Result</h3>
                                <p class="text-sm mt-2 mb-2">${result.display_name || query}</p>
                                <div class="text-xs text-gray-600 mb-2">
                                    <p>📍 Lat: ${lat.toFixed(6)}</p>
                                    <p>📍 Lng: ${lon.toFixed(6)}</p>
                                </div>
                                <button onclick="globalMap.setView([${lat}, ${lon}], 18)" class="bg-primary text-white px-2 py-1 rounded text-xs hover:bg-blue-700 mr-1">
                                    Zoom In
                                </button>
                                <button onclick="searchNearbyLocation(${lat}, ${lon})" class="bg-accent text-white px-2 py-1 rounded text-xs hover:bg-green-700">
                                    Explore Area
                                </button>
                            </div>
                        `)
                        .openPopup();
                    
                    console.log('✅ Search marker added successfully');
                } else {
                    console.error('❌ Map instance not available for setting view');
                }
                
                showSearchMessage(`✅ Found: ${result.display_name || query}`, 'success');
            } else {
                // Fallback: Try offline search for common Philippine locations
                console.log('🔄 Trying offline fallback search...');
                result = searchOfflineLocations(query);
                
                if (result) {
                    console.log('✅ Found offline result:', result);
                    
                    // Update map view with offline result
                    if (globalMap && typeof globalMap.setView === 'function') {
                        const lat = parseFloat(result.lat);
                        const lon = parseFloat(result.lon);
                        
                        globalMap.setView([lat, lon], 12);
                        
                        // Remove existing search marker
                        if (searchMarker) {
                            globalMap.removeLayer(searchMarker);
                        }
                        
                        // Add new search marker
                        searchMarker = L.marker([lat, lon], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowSize: [41, 41]
                            })
                        })
                            .addTo(globalMap)
                            .bindPopup(`
                                <div class="p-3">
                                    <h3 class="font-bold text-lg" style="color: #4caf50;">📍 ${result.display_name}</h3>
                                    <p class="text-sm mt-2 mb-2">Popular location in the Philippines</p>
                                    <div class="text-xs text-gray-600 mb-2">
                                        <p>📍 Lat: ${lat.toFixed(6)}</p>
                                        <p>📍 Lng: ${lon.toFixed(6)}</p>
                                    </div>
                                    <button onclick="globalMap.setView([${lat}, ${lon}], 15)" class="bg-primary text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                                        Zoom In
                                    </button>
                                </div>
                            `)
                            .openPopup();
                    }
                    
                    showSearchMessage(`✅ Found: ${result.display_name} (Offline)`, 'success');
                } else {
                    console.log('❌ No results found anywhere for query:', query);
                    showSearchMessage('❌ Location not found. Try searching for: Manila, Quezon City, Makati, Cebu, Davao, or other major Philippine cities.', 'error');
                }
            }
        } catch (error) {
            console.error('❌ Search error:', error);
            showSearchMessage('🔄 Search service temporarily unavailable. Please try again in a moment.', 'error');
        }
    }
    
    // Show search messages
    function showSearchMessage(message, type) {
        let messageDiv = document.getElementById('search-message');
        
        // Create message div if it doesn't exist
        if (!messageDiv) {
            messageDiv = document.createElement('div');
            messageDiv.id = 'search-message';
            messageDiv.className = 'mt-2 p-2 rounded text-sm';
            searchInput.parentNode.appendChild(messageDiv);
        }
        
        messageDiv.classList.remove('hidden');
        
        // Set appropriate styling based on message type
        if (type === 'success') {
            messageDiv.className = 'mt-2 p-2 rounded text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800';
        } else if (type === 'error') {
            messageDiv.className = 'mt-2 p-2 rounded text-sm bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800';
        } else {
            messageDiv.className = 'mt-2 p-2 rounded text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800';
        }
        
        messageDiv.textContent = message;
        
        // Auto-hide success and info messages
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, type === 'success' ? 3000 : 1500);
        }
    }
    
    // Event listeners
    searchBtn.addEventListener('click', performSearchEnhanced);
    clearBtn.addEventListener('click', clearSearch);
    
    // Allow Enter key to trigger search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearchEnhanced();
        }
    });
    
    // Initialize map interactions when map becomes available
    setTimeout(async () => {
        try {
            await waitForMap();
            if (globalMap && !globalMap._interactionsInitialized) {
                initializeMapInteractions();
                globalMap._interactionsInitialized = true;
                console.log('✅ Map interactions initialized on startup');
            }
        } catch (error) {
            console.warn('⚠️ Could not initialize map interactions on startup:', error);
        }
    }, 2000);
    
    // Offline search for common Philippine locations
    function searchOfflineLocations(query) {
        const commonLocations = {
            // Major Cities
            'manila': { lat: 14.5995, lon: 120.9842, display_name: 'Manila, Metro Manila, Philippines' },
            'quezon city': { lat: 14.6760, lon: 121.0437, display_name: 'Quezon City, Metro Manila, Philippines' },
            'makati': { lat: 14.5547, lon: 121.0244, display_name: 'Makati, Metro Manila, Philippines' },
            'pasig': { lat: 14.5764, lon: 121.0851, display_name: 'Pasig, Metro Manila, Philippines' },
            'taguig': { lat: 14.5176, lon: 121.0509, display_name: 'Taguig, Metro Manila, Philippines' },
            'caloocan': { lat: 14.6507, lon: 120.9676, display_name: 'Caloocan, Metro Manila, Philippines' },
            'marikina': { lat: 14.6507, lon: 121.1029, display_name: 'Marikina, Metro Manila, Philippines' },
            'mandaluyong': { lat: 14.5794, lon: 121.0359, display_name: 'Mandaluyong, Metro Manila, Philippines' },
            'san juan': { lat: 14.6019, lon: 121.0355, display_name: 'San Juan, Metro Manila, Philippines' },
            'pasay': { lat: 14.5378, lon: 120.9896, display_name: 'Pasay, Metro Manila, Philippines' },
            'paranaque': { lat: 14.4793, lon: 121.0198, display_name: 'Parañaque, Metro Manila, Philippines' },
            'las pinas': { lat: 14.4304, lon: 120.9822, display_name: 'Las Piñas, Metro Manila, Philippines' },
            'muntinlupa': { lat: 14.3832, lon: 121.0409, display_name: 'Muntinlupa, Metro Manila, Philippines' },
            'valenzuela': { lat: 14.7006, lon: 120.9822, display_name: 'Valenzuela, Metro Manila, Philippines' },
            'malabon': { lat: 14.6648, lon: 120.9668, display_name: 'Malabon, Metro Manila, Philippines' },
            'navotas': { lat: 14.6691, lon: 120.9472, display_name: 'Navotas, Metro Manila, Philippines' },
            
            // Other Major Cities
            'cebu': { lat: 10.3157, lon: 123.8854, display_name: 'Cebu City, Cebu, Philippines' },
            'davao': { lat: 7.1907, lon: 125.4553, display_name: 'Davao City, Davao del Sur, Philippines' },
            'iloilo': { lat: 10.7202, lon: 122.5621, display_name: 'Iloilo City, Iloilo, Philippines' },
            'cagayan de oro': { lat: 8.4542, lon: 124.6319, display_name: 'Cagayan de Oro, Misamis Oriental, Philippines' },
            'bacolod': { lat: 10.6770, lon: 122.9540, display_name: 'Bacolod, Negros Occidental, Philippines' },
            'zamboanga': { lat: 6.9214, lon: 122.0790, display_name: 'Zamboanga City, Zamboanga del Sur, Philippines' },
            'antipolo': { lat: 14.5873, lon: 121.1759, display_name: 'Antipolo, Rizal, Philippines' },
            'tarlac': { lat: 15.4817, lon: 120.5979, display_name: 'Tarlac City, Tarlac, Philippines' },
            'baguio': { lat: 16.4023, lon: 120.5960, display_name: 'Baguio, Benguet, Philippines' },
            'lipa': { lat: 13.9411, lon: 121.1650, display_name: 'Lipa, Batangas, Philippines' },
            
            // Popular Areas
            'bgc': { lat: 14.5507, lon: 121.0494, display_name: 'Bonifacio Global City, Taguig, Philippines' },
            'ortigas': { lat: 14.5866, lon: 121.0565, display_name: 'Ortigas Center, Pasig, Philippines' },
            'alabang': { lat: 14.4198, lon: 121.0387, display_name: 'Alabang, Muntinlupa, Philippines' },
            'eastwood': { lat: 14.6091, lon: 121.0773, display_name: 'Eastwood City, Quezon City, Philippines' },
            'araneta': { lat: 14.6255, lon: 121.0364, display_name: 'Araneta Coliseum, Quezon City, Philippines' },
            'sm mall of asia': { lat: 14.5352, lon: 120.9822, display_name: 'SM Mall of Asia, Pasay, Philippines' },
            'greenbelt': { lat: 14.5530, lon: 121.0244, display_name: 'Greenbelt, Makati, Philippines' },
            'ayala': { lat: 14.5530, lon: 121.0244, display_name: 'Ayala Center, Makati, Philippines' }
        };
        
        const searchTerm = query.toLowerCase().trim();
        
        // Direct match
        if (commonLocations[searchTerm]) {
            return commonLocations[searchTerm];
        }
        
        // Partial match
        for (const [key, location] of Object.entries(commonLocations)) {
            if (key.includes(searchTerm) || searchTerm.includes(key)) {
                return location;
            }
        }
        
        // Alternative names and abbreviations
        const alternatives = {
            'qc': 'quezon city',
            'bgc': 'bgc',
            'ortigas center': 'ortigas',
            'sm moa': 'sm mall of asia',
            'ayala center': 'ayala',
            'bonifacio global city': 'bgc',
            'fort bonifacio': 'bgc',
            'the fort': 'bgc'
        };
        
        if (alternatives[searchTerm] && commonLocations[alternatives[searchTerm]]) {
            return commonLocations[alternatives[searchTerm]];
        }
        
        return null;
    }
    
    console.log('✅ Map search initialized successfully!');
}

// Chatbot Functionality
function initializeChatbot() {
    console.log('🤖 Initializing chatbot...');
    
    // Wait a bit for DOM to be fully ready
    setTimeout(() => {
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbotWindow = document.getElementById('chatbot-window');
        const chatbotClose = document.getElementById('chatbot-close');
        const chatbotMinimize = document.getElementById('chatbot-minimize');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        const chatMessages = document.getElementById('chat-messages');
        const chatScrollDown = document.getElementById('chat-scroll-down');
        
        console.log('Chatbot elements found:', {
            toggle: !!chatbotToggle,
            window: !!chatbotWindow,
            close: !!chatbotClose,
            minimize: !!chatbotMinimize,
            input: !!chatInput,
            send: !!chatSend,
            messages: !!chatMessages,
            scrollDown: !!chatScrollDown
        });
        
        if (!chatbotToggle) {
            console.error('❌ Chatbot toggle button not found!');
            return;
        }
        
        if (!chatbotWindow) {
            console.error('❌ Chatbot window not found!');
            return;
        }

        let isOpen = false;

        // Toggle chatbot
        function toggleChatbot() {
            isOpen = !isOpen;
            if (isOpen) {
                chatbotWindow.classList.remove('hidden');
                if (chatInput) chatInput.focus();
            } else {
                chatbotWindow.classList.add('hidden');
            }
        }

        // Add message to chat
        function addMessage(message, isUser = false, showQuickReplies = false) {
            if (!chatMessages) return;
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;
            
            const messageBubble = document.createElement('div');
            messageBubble.className = `max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                isUser 
                    ? 'bg-primary text-white' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
            }`;
            
            // Handle line breaks in messages
            messageBubble.innerHTML = message.replace(/\n/g, '<br>');
            
            messageDiv.appendChild(messageBubble);
            chatMessages.appendChild(messageDiv);
            
            // Add quick reply buttons after bot messages
            if (!isUser && showQuickReplies) {
                addQuickReplyButtons();
            }
            
            // Scroll to bottom and manage scroll button
            scrollToBottom();
            updateScrollButton();
        }

        // Add quick reply buttons
        function addQuickReplyButtons() {
            const quickRepliesDiv = document.createElement('div');
            quickRepliesDiv.className = 'mb-4 px-2';
            quickRepliesDiv.innerHTML = `
                <div class="grid grid-cols-2 gap-2 mt-3">
                    <button class="chat-quick-reply bg-blue-50 dark:bg-blue-900 hover:bg-accent hover:text-white dark:hover:bg-green-800 text-blue-800 dark:text-blue-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-blue-200 dark:border-blue-700" 
                            data-reply="services" data-message="Tell me about your services">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                            </svg>
                            Services
                        </div>
                    </button>
                    
                    <button class="chat-quick-reply bg-green-50 dark:bg-green-900 hover:bg-accent hover:text-white dark:hover:bg-green-800 text-green-primary dark:text-green-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-accent" 
                            data-reply="contact" data-message="How can I contact you?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-accent dark:text-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Contact
                        </div>
                    </button>
                    
                    <button class="chat-quick-reply bg-purple-50 dark:bg-purple-900 hover:bg-purple-200 dark:hover:bg-purple-800 text-purple-800 dark:text-purple-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-purple-200 dark:border-purple-700" 
                            data-reply="location" data-message="Where are you located?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Location
                        </div>
                    </button>
                    
                    <button class="chat-quick-reply bg-orange-50 dark:bg-orange-900 hover:bg-orange-200 dark:hover:bg-orange-800 text-orange-800 dark:text-orange-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-orange-200 dark:border-orange-700" 
                            data-reply="hours" data-message="What are your operating hours?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Hours
                        </div>
                    </button>
                    
                    <button class="chat-quick-reply bg-red-50 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-800 dark:text-red-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-red-200 dark:border-red-700" 
                            data-reply="emergency" data-message="Do you have emergency services?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Emergency
                        </div>
                    </button>
                    
                    <button class="chat-quick-reply bg-indigo-50 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 text-indigo-800 dark:text-indigo-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-indigo-200 dark:border-indigo-700" 
                            data-reply="appointment" data-message="How do I schedule an appointment?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Appointment
                        </div>
                    </button>
                    
                    <button class="chat-quick-reply bg-teal-50 dark:bg-teal-900 hover:bg-teal-200 dark:hover:bg-teal-800 text-teal-800 dark:text-teal-200 p-2 rounded text-left text-xs transition-colors duration-200 border border-teal-200 dark:border-teal-700" 
                            data-reply="permits" data-message="Tell me about permits and inspections">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Permits
                        </div>
                    </button>
                </div>
            `;
            
            chatMessages.appendChild(quickRepliesDiv);
            
            // Add event listeners to new quick reply buttons
            const newButtons = quickRepliesDiv.querySelectorAll('.chat-quick-reply');
            newButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const replyType = this.getAttribute('data-reply');
                    const message = this.getAttribute('data-message');
                    if (replyType && message) {
                        handleQuickReply(replyType, message);
                    }
                });
            });
        }

        // Scroll to bottom function
        function scrollToBottom() {
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Create floating scroll down button if it doesn't exist
        function createFloatingScrollButton() {
            // Remove existing scroll button if any
            const existingBtn = chatMessages.parentElement.querySelector('.floating-scroll-btn');
            if (existingBtn) {
                existingBtn.remove();
            }
            
            // Create floating scroll down button
            const floatingScrollBtn = document.createElement('div');
            floatingScrollBtn.className = 'floating-scroll-btn fixed bottom-20 right-4 bg-primary hover:bg-blue-700 text-white p-3 rounded-full shadow-lg cursor-pointer transition-all duration-300 opacity-0 invisible z-50';
            floatingScrollBtn.style.cssText = `
                position: absolute;
                bottom: 60px;
                left: 50%;
                transform: translateX(-50%);
                width: 40px;
                height: 40px;
                background: #4a90e2;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
                cursor: pointer;
                transition: all 0.3s ease;
                opacity: 0;
                visibility: hidden;
                z-index: 1000;
                border: 2px solid rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
            `;
            
            floatingScrollBtn.innerHTML = `
                <svg class="w-5 h-5 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            `;
            
            // Add hover effects
            floatingScrollBtn.addEventListener('mouseenter', function() {
                this.style.background = '#357abd';
                this.style.transform = 'translateX(-50%) scale(1.1)';
                this.style.boxShadow = '0 6px 20px rgba(74, 144, 226, 0.4)';
            });
            
            floatingScrollBtn.addEventListener('mouseleave', function() {
                this.style.background = '#4a90e2';
                this.style.transform = 'translateX(-50%) scale(1)';
                this.style.boxShadow = '0 4px 12px rgba(74, 144, 226, 0.3)';
            });
            
            // Add click handler
            floatingScrollBtn.addEventListener('click', function() {
                scrollToBottom();
                this.style.opacity = '0';
                this.style.visibility = 'hidden';
            });
            
            // Append to chat messages container
            if (chatMessages && chatMessages.parentElement) {
                chatMessages.parentElement.style.position = 'relative';
                chatMessages.parentElement.appendChild(floatingScrollBtn);
            }
            
            return floatingScrollBtn;
        }

        // Update scroll button visibility
        function updateScrollButton() {
            let floatingBtn = chatMessages?.parentElement?.querySelector('.floating-scroll-btn');
            
            // Create button if it doesn't exist
            if (!floatingBtn && chatMessages) {
                floatingBtn = createFloatingScrollButton();
            }
            
            if (floatingBtn && chatMessages) {
                const isAtBottom = chatMessages.scrollTop >= chatMessages.scrollHeight - chatMessages.clientHeight - 100;
                
                if (isAtBottom) {
                    // Hide button with smooth animation
                    floatingBtn.style.opacity = '0';
                    floatingBtn.style.visibility = 'hidden';
                    floatingBtn.style.transform = 'translateX(-50%) scale(0.8)';
                } else {
                    // Show button with smooth animation
                    floatingBtn.style.opacity = '1';
                    floatingBtn.style.visibility = 'visible';
                    floatingBtn.style.transform = 'translateX(-50%) scale(1)';
                }
            }
        }

        // Bot responses
        const botResponses = {
            'services': 'We offer comprehensive health and sanitation services including:\n• Medical consultations and emergency care\n• Preventive healthcare programs\n• Sanitation permits and inspections\n• Immunizations and nutrition monitoring\n• Wastewater and septic services\n\nHow can I help you with any specific service?',
            'contact': 'You can reach us at:\n📍 8th Ave, Grace Park East, Caloocan, Metro Manila\n📞 09234662520\n📧 info@healthsanitation.com\n🕒 Monday-Friday: 8:00 AM - 5:00 PM (Open)\n🕒 Saturday & Sunday: Closed',
            'location': 'We are located at 8th Ave, Grace Park East, Caloocan, Metro Manila, Philippines. You can find us on the map in our contact section above.',
            'hours': 'Our office hours are:\n• Monday - Friday: 8:00 AM - 5:00 PM (Open)\n• Saturday: Closed\n• Sunday: Closed\n\nFor emergencies, please call 09234662520 anytime.',
            'emergency': 'Yes, we provide 24/7 emergency medical services!\n\n🚨 Call us immediately at 09234662520 for:\n• Urgent health situations\n• Accidents and injuries\n• Critical care needs\n• Medical emergencies\n\nOur trained emergency response teams are ready to help.',
            'appointment': 'To schedule an appointment:\n\n1️⃣ Fill out our appointment form on this website\n2️⃣ Call us at 09234662520\n3️⃣ Visit our office during business hours\n\n📋 Please bring:\n• Valid ID\n• Medical documents (if any)\n• Arrive 15 minutes early',
            'permits': 'We handle all sanitation permits and inspections:\n\n🏢 Business permits\n🍽️ Food service establishment permits\n🔍 Health inspections\n📋 Compliance documentation\n🏭 Commercial facility certifications\n\nContact us for permit applications and renewals!',
            'default': 'Thank you for your message! I can help you with information about our services, contact details, appointments, permits, emergency care, and more. What would you like to know?'
        };

        // Process user message
        function processMessage(message) {
            const lowerMessage = message.toLowerCase();
            let response = botResponses.default;
            
            if (lowerMessage.includes('service') || lowerMessage.includes('what do you do')) {
                response = botResponses.services;
            } else if (lowerMessage.includes('contact') || lowerMessage.includes('phone') || lowerMessage.includes('email')) {
                response = botResponses.contact;
            } else if (lowerMessage.includes('location') || lowerMessage.includes('address') || lowerMessage.includes('where')) {
                response = botResponses.location;
            } else if (lowerMessage.includes('hours') || lowerMessage.includes('time') || lowerMessage.includes('open')) {
                response = botResponses.hours;
            } else if (lowerMessage.includes('emergency') || lowerMessage.includes('urgent')) {
                response = botResponses.emergency;
            } else if (lowerMessage.includes('appointment') || lowerMessage.includes('schedule') || lowerMessage.includes('book')) {
                response = botResponses.appointment;
            } else if (lowerMessage.includes('permit') || lowerMessage.includes('inspection') || lowerMessage.includes('license')) {
                response = botResponses.permits;
            } else if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
                response = 'Hello! Welcome to Health & Sanitation Services. I\'m here to help you with information about our services, appointments, and more. What can I assist you with today?';
            }
            
            return response;
        }

        // Send message
        function sendMessage() {
            if (!chatInput) return;
            
            const message = chatInput.value.trim();
            if (!message) return;
            
            // Add user message
            addMessage(message, true);
            chatInput.value = '';
            
            // Show typing indicator and respond
            setTimeout(() => {
                const response = processMessage(message);
                addMessage(response, false, true); // Show quick replies after response
            }, 1000);
        }

        // Handle quick reply buttons
        function handleQuickReply(replyType, message) {
            if (!isOpen) {
                toggleChatbot(); // Open chatbot if closed
            }
            
            // Add user message
            addMessage(message, true);
            
            // Show bot response with quick replies
            setTimeout(() => {
                const response = botResponses[replyType] || botResponses.default;
                addMessage(response, false, true); // Always show quick replies after bot response
            }, 1000);
        }

        // Add initial welcome message with quick replies
        function initializeChatWelcome() {
            // Clear existing messages
            if (chatMessages) {
                chatMessages.innerHTML = `
                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg transition-colors duration-300 mb-4">
                        <p class="text-sm text-gray-800 dark:text-gray-200">Hello! I'm your Health Assistant. How can I help you today?</p>
                    </div>
                `;
                // Add initial quick reply buttons
                addQuickReplyButtons();
            }
        }

        // Event listeners
        chatbotToggle.addEventListener('click', () => {
            toggleChatbot();
            // Initialize welcome message when first opened
            if (isOpen && chatMessages && chatMessages.children.length <= 1) {
                setTimeout(() => {
                    initializeChatWelcome();
                }, 300);
            }
        });
        
        if (chatbotClose) {
            chatbotClose.addEventListener('click', () => {
                chatbotWindow.classList.add('hidden');
                isOpen = false;
            });
        }

        if (chatSend) {
            chatSend.addEventListener('click', sendMessage);
        }

        if (chatInput) {
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        // Initialize floating scroll button
        if (chatMessages) {
            // Create the floating scroll button
            createFloatingScrollButton();
            
            // Monitor scroll position for floating scroll button
            chatMessages.addEventListener('scroll', function() {
                updateScrollButton();
            });
            
            // Initial check for scroll button visibility
            setTimeout(() => {
                updateScrollButton();
            }, 500);
        }

        // Handle original scroll down button if it exists (fallback)
        if (chatScrollDown) {
            chatScrollDown.addEventListener('click', function() {
                scrollToBottom();
                chatScrollDown.classList.add('hidden');
            });
        }

        // Handle quick reply buttons (existing ones in HTML)
        const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
        quickReplyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const replyType = this.getAttribute('data-reply');
                const message = this.getAttribute('data-message');
                if (replyType && message) {
                    handleQuickReply(replyType, message);
                }
            });
        });

        console.log('✅ Chatbot initialized successfully!');
    }, 1000);
}

// Scroll to Top Functionality
function initializeScrollToTop() {
    console.log('⬆️ Initializing scroll to top...');
    
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (!scrollToTopBtn) {
        console.warn('⚠️ Scroll to top button not found');
        return;
    }
    
    // Show/hide scroll to top button based on scroll position
    function toggleScrollButton() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.remove('opacity-0', 'invisible');
            scrollToTopBtn.classList.add('opacity-100', 'visible');
        } else {
            scrollToTopBtn.classList.add('opacity-0', 'invisible');
            scrollToTopBtn.classList.remove('opacity-100', 'visible');
        }
    }
    
    // Smooth scroll to top function
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Event listeners
    window.addEventListener('scroll', toggleScrollButton);
    scrollToTopBtn.addEventListener('click', scrollToTop);
    
    // Initial check
    toggleScrollButton();
    
    console.log('✅ Scroll to top initialized successfully!');
}

// Language Translation System
function initializeLanguageSystem() {
    console.log('🌐 Initializing language system...');
    
    // Translation data
    const translations = {
        en: {
            // Navigation
            home: "Home",
            about: "About",
            services: "Services",
            contact: "Contact",
            login: "Login",
            register: "Register",
            
            // Hero Section
            heroTitle: "Health & Sanitation Services",
            heroSubtitle: "Promoting community health and environmental safety",
            getStarted: "Get Started",
            
            // About Section
            aboutTitle: "About Us",
            aboutText1: "We are dedicated to providing comprehensive health and sanitation services to our community. Our mission is to ensure the well-being of all residents through quality healthcare, environmental protection, and public health initiatives that promote a safer, healthier living environment for everyone.",
            aboutText2: "With over 15 years of experience and a team of qualified professionals including licensed healthcare workers, environmental specialists, and certified sanitation experts, we strive to maintain the highest standards in public health and sanitation services. Our dedicated staff works around the clock to ensure community safety and wellness.",
            vision: "Our Vision",
            visionText: "To create a healthier and safer community through innovative health and sanitation solutions, advanced technology integration, and sustainable environmental practices that protect and enhance the quality of life for current and future generations.",
            ourValues: "Our Values",
            excellence: "Excellence",
            excellenceDesc: "Delivering the highest quality services",
            integrity: "Integrity",
            integrityDesc: "Transparent and ethical practices",
            community: "Community",
            communityDesc: "Serving with compassion and dedication",
            innovation: "Innovation",
            innovationDesc: "Embracing modern solutions",
            ourImpact: "Our Impact",
            yearsOfService: "Years of Service",
            peopleServed: "People Served",
            healthInspections: "Health Inspections",
            emergencyResponses: "Emergency Response",
            
            // Services
            healthCenterTitle: "Health Center Services",
            medicalConsultations: "Medical Consultations",
            medicalConsultationsDesc: "Professional medical consultations with qualified healthcare providers for general health concerns, routine check-ups, and specialized medical advice.",
            medicalConsultationsList1: "General practice consultations",
            medicalConsultationsList2: "Specialist referrals",
            medicalConsultationsList3: "Health assessments",
            medicalConsultationsList4: "Medical certificates",
            emergencyCare: "Emergency Care",
            emergencyCareDesc: "24/7 emergency medical services for urgent health situations, accidents, and critical care needs with trained emergency response teams.",
            emergencyCareList1: "24/7 emergency response",
            emergencyCareList2: "First aid and trauma care",
            emergencyCareList3: "Ambulance services",
            emergencyCareList4: "Critical care stabilization",
            preventiveCare: "Preventive Care",
            preventiveCareDesc: "Regular health screenings, preventive care programs, and wellness initiatives to maintain optimal health and prevent diseases.",
            preventiveCareList1: "Annual health screenings",
            preventiveCareList2: "Vaccination programs",
            preventiveCareList3: "Health education workshops",
            preventiveCareList4: "Wellness monitoring",
            
            // Sanitation Services
            sanitationPermitTitle: "Sanitation Permit & Inspection Services",
            businessPermits: "Business Permits",
            businessPermitsDesc: "Comprehensive sanitation permit processing for businesses, restaurants, food establishments, and commercial facilities to ensure compliance with health standards.",
            businessPermitsList1: "New business permit applications",
            businessPermitsList2: "Permit renewals and updates",
            businessPermitsList3: "Food service establishment permits",
            businessPermitsList4: "Commercial facility certifications",
            businessPermitsList5: "Compliance documentation",
            healthInspectionsTitle: "Health Inspections",
            healthInspectionsDesc: "Professional health and sanitation inspections to ensure facilities meet safety standards and regulatory requirements for public health protection.",
            healthInspectionsList1: "Routine facility inspections",
            healthInspectionsList2: "Food safety assessments",
            healthInspectionsList3: "Water quality testing",
            healthInspectionsList4: "Waste management evaluation",
            healthInspectionsList5: "Compliance reporting",
            
            // Immunization
            immunizationTitle: "Immunization & Nutrition Tracker",
            
            // Common
            search: "Search",
            clear: "Clear",
            send: "Send",
            
            // Chat
            chatWelcome: "Hello! I'm your Health Assistant. How can I help you today?",
            chatPlaceholder: "Type your message...",
            searchPlaceholder: "Search for address..."
        },
        
        fil: {
            // Navigation
            home: "Tahanan",
            about: "Tungkol",
            services: "Mga Serbisyo",
            contact: "Makipag-ugnayan",
            login: "Mag-login",
            register: "Mag-rehistro",
            
            // Hero Section
            heroTitle: "Mga Serbisyo sa Kalusugan at Kalinisan",
            heroSubtitle: "Pagsusulong ng kalusugan ng komunidad at kaligtasan ng kapaligiran",
            getStarted: "Magsimula",
            
            // About Section
            aboutTitle: "Tungkol sa Amin",
            aboutText1: "Kami ay nakatuon sa pagbibigay ng komprehensibong mga serbisyo sa kalusugan at kalinisan sa aming komunidad. Ang aming misyon ay tiyakin ang kapakanan ng lahat ng mga residente sa pamamagitan ng de-kalidad na pangangalagang pangkalusugan, proteksyon sa kapaligiran, at mga inisyatiba sa pampublikong kalusugan na nagsusulong ng mas ligtas at mas malusog na kapaligiran para sa lahat.",
            aboutText2: "Sa mahigit 15 taong karanasan at isang koponan ng mga kwalipikadong propesyonal kabilang ang mga lisensyadong manggagawa sa kalusugan, mga espesyalista sa kapaligiran, at mga sertipikadong eksperto sa kalinisan, nagsusumikap kaming mapanatili ang pinakamataas na mga pamantayan sa mga serbisyo sa pampublikong kalusugan at kalinisan.",
            vision: "Aming Pananaw",
            visionText: "Lumikha ng mas malusog at mas ligtas na komunidad sa pamamagitan ng mga makabagong solusyon sa kalusugan at kalinisan, advanced na pagsasama ng teknolohiya, at mga sustainable na gawi sa kapaligiran na nagpoprotekta at nagpapahusay sa kalidad ng buhay para sa kasalukuyan at mga susunod na henerasyon.",
            ourValues: "Aming mga Pagpapahalaga",
            excellence: "Kahusayan",
            excellenceDesc: "Paghahatid ng pinakamataas na kalidad ng mga serbisyo",
            integrity: "Integridad",
            integrityDesc: "Transparent at etikal na mga gawi",
            community: "Komunidad",
            communityDesc: "Paglilingkod nang may habag at dedikasyon",
            innovation: "Pagbabago",
            innovationDesc: "Pagtanggap sa mga modernong solusyon",
            ourImpact: "Aming Epekto",
            yearsOfService: "Taon ng Serbisyo",
            peopleServed: "Taong Nasilbihan",
            healthInspections: "Mga Inspeksyon sa Kalusugan",
            emergencyResponses: "Tugon sa Emergency",
            
            // Services
            healthCenterTitle: "Mga Serbisyo ng Health Center",
            medicalConsultations: "Mga Konsultasyong Medikal",
            medicalConsultationsDesc: "Propesyonal na mga konsultasyong medikal kasama ang mga kwalipikadong healthcare provider para sa mga pangkalahatang alalahanin sa kalusugan, regular na check-up, at espesyalisadong payo sa medisina.",
            emergencyCare: "Pangangalaga sa Emergency",
            emergencyCareDesc: "24/7 emergency medical services para sa mga urgent na sitwasyon sa kalusugan, aksidente, at mga pangangailangang critical care kasama ang mga nakatraing emergency response team.",
            preventiveCare: "Preventive Care",
            preventiveCareDesc: "Regular na mga health screening, preventive care program, at mga wellness initiative upang mapanatili ang optimal na kalusugan at maiwasan ang mga sakit.",
            
            // Sanitation Services
            sanitationPermitTitle: "Mga Serbisyo sa Sanitation Permit at Inspeksyon",
            businessPermits: "Mga Business Permit",
            businessPermitsDesc: "Komprehensibong proseso ng sanitation permit para sa mga negosyo, restaurant, food establishment, at commercial facility upang matiyak ang pagsunod sa mga pamantayan sa kalusugan.",
            healthInspectionsTitle: "Mga Inspeksyon sa Kalusugan",
            healthInspectionsDesc: "Propesyonal na mga inspeksyon sa kalusugan at kalinisan upang matiyak na ang mga pasilidad ay nakakatugon sa mga pamantayan sa kaligtasan at regulatory requirement para sa proteksyon ng pampublikong kalusugan.",
            
            // Immunization
            immunizationTitle: "Immunization at Nutrition Tracker",
            
            // Common
            search: "Maghanap",
            clear: "Linisin",
            send: "Ipadala",
            
            // Chat
            chatWelcome: "Kumusta! Ako ang inyong Health Assistant. Paano ko kayo matutulungan ngayon?",
            chatPlaceholder: "I-type ang inyong mensahe...",
            searchPlaceholder: "Maghanap ng address..."
        },
        
        ceb: {
            // Navigation
            home: "Balay",
            about: "Mahitungod",
            services: "Mga Serbisyo",
            contact: "Kontak",
            login: "Pag-login",
            register: "Pag-rehistro",
            
            // Hero Section
            heroTitle: "Mga Serbisyo sa Panglawas ug Kalimpyo",
            heroSubtitle: "Pagpalambo sa panglawas sa komunidad ug kaluwasan sa palibot",
            getStarted: "Pagsugod",
            
            // About Section
            aboutTitle: "Mahitungod Kanamo",
            aboutText1: "Kami nakatutok sa paghatag og komprehensibong mga serbisyo sa panglawas ug kalimpyo sa among komunidad. Ang among misyon mao ang pagsiguro sa kaayohan sa tanan nga mga residente pinaagi sa kalidad nga pag-atiman sa panglawas, proteksyon sa palibot, ug mga inisyatiba sa publikong panglawas nga nagpalambo og mas luwas ug mas himsog nga palibot alang sa tanan.",
            aboutText2: "Uban sa sobra sa 15 ka tuig nga kasinatian ug usa ka team sa mga kwalipikadong propesyonal lakip ang mga lisensyadong trabahante sa panglawas, mga espesyalista sa palibot, ug mga sertipikadong eksperto sa kalimpyo, naningkamot kami nga mapadayon ang pinakataas nga mga sumbanan sa mga serbisyo sa publikong panglawas ug kalimpyo.",
            vision: "Among Panan-aw",
            visionText: "Paghimo og mas himsog ug mas luwas nga komunidad pinaagi sa mga bag-ong solusyon sa panglawas ug kalimpyo, advanced nga paghiusa sa teknolohiya, ug mga malungtarong gawi sa palibot nga nagpanalipod ug nagpauswag sa kalidad sa kinabuhi alang sa karon ug sa umaabot nga mga henerasyon.",
            ourValues: "Among mga Mithi",
            excellence: "Kahanas",
            excellenceDesc: "Paghatag sa pinakataas nga kalidad sa mga serbisyo",
            integrity: "Integridad",
            integrityDesc: "Tin-aw ug etikal nga mga gawi",
            community: "Komunidad",
            communityDesc: "Pag-alagad uban ang kalooy ug dedikasyon",
            innovation: "Kabag-ohan",
            innovationDesc: "Pagdawat sa mga modernong solusyon",
            ourImpact: "Among Epekto",
            yearsOfService: "Ka-tuig sa Serbisyo",
            peopleServed: "Tawo nga Naalagaan",
            healthInspections: "Mga Inspeksyon sa Panglawas",
            emergencyResponses: "Tubag sa Emergency",
            
            // Services
            healthCenterTitle: "Mga Serbisyo sa Health Center",
            medicalConsultations: "Mga Konsultasyon sa Medisina",
            medicalConsultationsDesc: "Propesyonal nga mga konsultasyon sa medisina uban sa mga kwalipikadong healthcare provider alang sa mga pangkalahatang kabalaka sa panglawas, regular nga check-up, ug espesyalisadong tambag sa medisina.",
            emergencyCare: "Pag-atiman sa Emergency",
            emergencyCareDesc: "24/7 emergency medical services alang sa mga urgent nga sitwasyon sa panglawas, aksidente, ug mga panginahanglan sa critical care uban sa mga natrain nga emergency response team.",
            preventiveCare: "Preventive Care",
            preventiveCareDesc: "Regular nga mga health screening, preventive care program, ug mga wellness initiative aron mapadayon ang optimal nga panglawas ug malikayan ang mga sakit.",
            
            // Sanitation Services
            sanitationPermitTitle: "Mga Serbisyo sa Sanitation Permit ug Inspeksyon",
            businessPermits: "Mga Business Permit",
            businessPermitsDesc: "Komprehensibong proseso sa sanitation permit alang sa mga negosyo, restaurant, food establishment, ug commercial facility aron masiguro ang pagsunod sa mga sumbanan sa panglawas.",
            healthInspectionsTitle: "Mga Inspeksyon sa Panglawas",
            healthInspectionsDesc: "Propesyonal nga mga inspeksyon sa panglawas ug kalimpyo aron masiguro nga ang mga pasilidad nakaabot sa mga sumbanan sa kaluwasan ug regulatory requirement alang sa proteksyon sa publikong panglawas.",
            
            // Immunization
            immunizationTitle: "Immunization ug Nutrition Tracker",
            
            // Common
            search: "Pangita",
            clear: "Limpyohi",
            send: "Ipadala",
            
            // Chat
            chatWelcome: "Kumusta! Ako ang inyong Health Assistant. Unsaon nako kamo matabangan karon?",
            chatPlaceholder: "I-type ang inyong mensahe...",
            searchPlaceholder: "Pangita og address..."
        }
    };
    
    let currentLanguage = 'en';
    
    // Get language selector
    const languageSelector = document.getElementById('language-selector');
    if (!languageSelector) {
        console.warn('⚠️ Language selector not found');
        return;
    }
    
    // Function to translate page
    function translatePage(language) {
        const elements = document.querySelectorAll('[data-translate]');
        const placeholderElements = document.querySelectorAll('[data-translate-placeholder]');
        
        elements.forEach(element => {
            const key = element.getAttribute('data-translate');
            if (translations[language] && translations[language][key]) {
                element.textContent = translations[language][key];
            }
        });
        
        placeholderElements.forEach(element => {
            const key = element.getAttribute('data-translate-placeholder');
            if (translations[language] && translations[language][key]) {
                element.placeholder = translations[language][key];
            }
        });
        
        currentLanguage = language;
        localStorage.setItem('selectedLanguage', language);
        console.log(`🌐 Page translated to: ${language}`);
    }
    
    // Load saved language
    const savedLanguage = localStorage.getItem('selectedLanguage');
    if (savedLanguage && translations[savedLanguage]) {
        currentLanguage = savedLanguage;
        languageSelector.value = savedLanguage;
        translatePage(savedLanguage);
    }
    
    // Language selector event listener
    languageSelector.addEventListener('change', function() {
        const selectedLanguage = this.value;
        translatePage(selectedLanguage);
    });
    
    console.log('✅ Language system initialized successfully!');
}

console.log('✅ All JavaScript components loaded successfully!');