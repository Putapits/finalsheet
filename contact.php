<?php 
// Start session and check login status BEFORE any output
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in and redirect to appropriate dashboard (only if no output yet)
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && !headers_sent()) {
    $redirect_url = Database::getRoleRedirect($_SESSION['role']);
    header('Location: ' . $redirect_url);
    exit();
}

include 'header.php'; 
?>

    <!-- Main Content -->
    <main class="pt-16">
        <!-- Contact Section -->
        <section id="contact" class="py-16 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white" data-translate="contactTitle">Contact Us</h2>
                
                <!-- Contact Form and Info Row -->
                <div class="grid lg:grid-cols-2 gap-12 mb-12">
                    <!-- Contact Form -->
                    <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600">
                        <h3 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white flex items-center">
                            <svg class="w-6 h-6 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Send us a Message
                        </h3>
                        
                        <form id="contact-form" class="space-y-6">
                            <!-- Name Fields -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name *</label>
                                    <input type="text" id="first-name" name="first-name" required
                                           class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                           placeholder="Enter your first name">
                                </div>
                                <div>
                                    <label for="last-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name *</label>
                                    <input type="text" id="last-name" name="last-name" required
                                           class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                           placeholder="Enter your last name">
                                </div>
                            </div>
                            
                            <!-- Email and Phone -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                                    <input type="email" id="email" name="email"
                                           class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                           placeholder="your.email@example.com" required>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone"
                                           class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                           placeholder="(+63)" required>
                                </div>
                            </div>
                            
                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject *</label>
                                <select id="subject" name="subject" required
                                        class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200">
                                    <option value="">Select a subject</option>
                                    <option value="health-center">Health Center Services</option>
                                    <option value="sanitation-permit">Sanitation Permit & Inspection</option>
                                    <option value="immunization">Immunization & Nutrition</option>
                                    <option value="wastewater">Wastewater & Septic Services</option>
                                    <option value="surveillance">Health Surveillance</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="feedback">Feedback</option>
                                </select>
                            </div>
                            
                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message *</label>
                                <textarea id="message" name="message" rows="5" required
                                          class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 resize-vertical"
                                          placeholder="Please describe your inquiry or message in detail..."></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <div>
                                <button type="submit" 
                                        class="w-full bg-primary hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-medium py-3 px-6 rounded-md transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Send Message
                                </button>
                            </div>
                            
                            <!-- Success/Error Messages -->
                            <div id="form-message" class="hidden p-4 rounded-md"></div>
                        </form>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="space-y-8">
                        <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600">
                            <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white flex items-center">
                                <svg class="w-6 h-6 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                Get in Touch
                            </h3>
                            <div class="space-y-6">
                                <div class="flex items-start space-x-3">
                                    <div class="text-blue-600 dark:text-blue-400 mt-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Address</p>
                                        <p class="text-gray-600 dark:text-gray-300">8th Ave, Grace Park East, Caloocan</p>
                                        <p class="text-gray-600 dark:text-gray-300">Metro Manila, Philippines</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="text-blue-600 dark:text-blue-400 mt-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Phone</p>
                                        <p class="text-gray-600 dark:text-gray-300">09234662520</p>
                                        <p class="text-gray-600 dark:text-gray-300">09234662520 (Emergency)</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="text-blue-600 dark:text-blue-400 mt-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Email</p>
                                        <p class="text-gray-600 dark:text-gray-300">info@healthsanitation.com</p>
                                        <p class="text-gray-600 dark:text-gray-300">emergency@healthsanitation.com</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="text-blue-600 dark:text-blue-400 mt-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Office Hours</p>
                                        <p class="text-gray-600 dark:text-gray-300">Monday - Friday: 8:00 AM - 5:00 PM (Open)</p>
                                        <p class="text-gray-600 dark:text-gray-300">Saturday: Closed</p>
                                        <p class="text-gray-600 dark:text-gray-300">Sunday: Closed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Response Time Info -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border border-blue-200 dark:border-blue-800">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Response Time
                            </h4>
                            <p class="text-blue-800 dark:text-blue-200 text-sm">
                                We typically respond to inquiries within 24 hours during business days. 
                                For urgent health matters, please call our emergency line or visit our facility directly.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Map Section -->
                <div class="bg-white dark:bg-gray-700 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-colors duration-300">
                    <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Find Our Location
                    </h3>
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" id="search-input" placeholder="Search for address..." 
                                   class="flex-1 p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200" 
                                   data-translate-placeholder="searchPlaceholder">
                            <div class="flex gap-2">
                                <button id="search-btn" class="bg-primary hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white px-6 py-3 rounded-md font-medium transition-colors duration-200" data-translate="search">Search</button>
                                <button id="clear-btn" class="bg-secondary hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-800 text-white px-6 py-3 rounded-md font-medium transition-colors duration-200" data-translate="clear">Clear</button>
                            </div>
                        </div>
                    </div>
                    <div id="map" class="h-96 lg:h-[500px] rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 relative z-10"></div>
                </div>
            </div>
        </section>
        <!-- Contact Form Submission Script (moved inside <main> for citizen portal inclusion) -->
        <script>
          document.addEventListener('DOMContentLoaded', function(){
            const form = document.getElementById('contact-form');
            const msg = document.getElementById('form-message');
            if (!form) return;
            form.addEventListener('submit', async function(e){
              e.preventDefault();
              if (msg) { msg.classList.add('hidden'); msg.textContent=''; msg.className='hidden p-4 rounded-md'; }
              const payload = {
                first_name: document.getElementById('first-name')?.value.trim() || '',
                last_name: document.getElementById('last-name')?.value.trim() || '',
                email: document.getElementById('email')?.value.trim() || '',
                phone: document.getElementById('phone')?.value.trim() || '',
                subject: document.getElementById('subject')?.value || '',
                message: document.getElementById('message')?.value.trim() || ''
              };
              try {
                // Robust endpoint resolution: works from /capstone-HS/contact.php and /capstone-HS/citizen/citizen.php
                const parts = window.location.pathname.split('/').filter(Boolean);
                const appRoot = '/' + (parts[0] || '');
                const endpoint = appRoot + '/contact_submit.php';
                const res = await fetch(endpoint, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify(payload)
                });
                const json = await res.json().catch(()=>({success:false,message:'Invalid server response'}));
                if (!res.ok || !json.success) throw new Error(json.message || ('HTTP '+res.status));
                if (msg) {
                  msg.textContent = json.message || 'Message sent!';
                  msg.className = 'p-4 rounded-md bg-green-100 text-green-800 border border-green-300';
                }
                form.reset();
              } catch (err) {
                if (msg) {
                  msg.textContent = err && err.message ? err.message : 'Failed to send message.';
                  msg.className = 'p-4 rounded-md bg-red-100 text-red-800 border border-red-300';
                }
              }
            });
          });
        </script>
        
        <!-- Map Initialization Script (moved inside <main> and loads Leaflet dynamically if missing) -->
        <script>
        console.log('=== CONTACT MAP DEBUG START ===');
        // Shared map state
        let map; 
        let searchMarker = null;
        const defaultCenter = [14.5995, 120.9842];
        const defaultZoom = 13;
        
        function ensureLeaflet() {
            return new Promise((resolve, reject) => {
                if (typeof L !== 'undefined') return resolve();
                // inject CSS if not present
                const leafletCssHref = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                const leafletJsSrc = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                if (!document.querySelector(`link[href="${leafletCssHref}"]`)) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = leafletCssHref;
                    document.head.appendChild(link);
                }
                if (!document.querySelector(`script[src="${leafletJsSrc}"]`)) {
                    const s = document.createElement('script');
                    s.src = leafletJsSrc;
                    s.onload = () => resolve();
                    s.onerror = () => reject(new Error('Failed to load Leaflet library'));
                    document.head.appendChild(s);
                } else {
                    // script tag exists but may not be loaded yet; wait for load or poll
                    const existing = document.querySelector(`script[src="${leafletJsSrc}"]`);
                    if (existing && existing.readyState && existing.readyState !== 'complete') {
                        existing.addEventListener('load', () => resolve());
                        existing.addEventListener('error', () => reject(new Error('Leaflet failed')));
                    } else {
                        // small wait to allow L to bind
                        const iv = setInterval(()=>{ if (typeof L !== 'undefined'){ clearInterval(iv); resolve(); } }, 50);
                        setTimeout(()=>{ clearInterval(iv); if (typeof L === 'undefined') reject(new Error('Leaflet unavailable')); }, 3000);
                    }
                }
            });
        }
        
        // Function to show status in map container
        function showMapStatus(message, isError = false) {
            const mapContainer = document.getElementById('map');
            if (mapContainer) {
                const bgColor = isError ? '#f8d7da' : '#d4edda';
                const textColor = isError ? '#721c24' : '#155724';
                const borderColor = isError ? '#f5c6cb' : '#c3e6cb';
                mapContainer.innerHTML = `
                    <div style="padding: 20px; background: ${bgColor}; color: ${textColor}; text-align: center; border: 2px solid ${borderColor}; border-radius: 8px; font-family: Arial, sans-serif; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <h3 style="margin: 0 0 10px 0;">${isError ? '❌ Error' : '✅ Status'}</h3>
                        <p style="margin: 0 0 10px 0;">${message}</p>
                        <small>Time: ${new Date().toLocaleTimeString()}</small>
                    </div>
                `;
            }
        }

        function initMap(){
            const mapContainer = document.getElementById('map');
            console.log('Map container found:', !!mapContainer);
            if (!mapContainer) {
                console.error('❌ Map container not found!');
                return;
            }
            // Force container styling
            mapContainer.style.height = '400px';
            mapContainer.style.width = '100%';
            mapContainer.style.position = 'relative';
            mapContainer.style.display = 'block';
            mapContainer.style.backgroundColor = '#f0f0f0';
            mapContainer.style.border = '2px solid #007cba';

            console.log('Container styled, checking Leaflet...');
            showMapStatus('Initializing map...', false);

            // Create the map
            setTimeout(function(){
                try {
                    console.log('Creating map instance...');
                    mapContainer.innerHTML = '';
                    map = L.map('map').setView(defaultCenter, defaultZoom);
                    console.log('✅ Map instance created');

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    console.log('✅ Tiles added');

                    L.marker(defaultCenter)
                        .addTo(map)
                        .bindPopup('Health & Sanitation Office')
                        .openPopup();
                    console.log('✅ Marker added');

                    // --- Search wiring (Nominatim) ---
                    const input = document.getElementById('search-input');
                    const btn = document.getElementById('search-btn');
                    const clearBtn = document.getElementById('clear-btn');

                    async function performSearch(){
                        const q = (input && input.value || '').trim();
                        if (!q) return;
                        try{
                            const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q='+encodeURIComponent(q);
                            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            const arr = await resp.json();
                            if (!Array.isArray(arr) || arr.length === 0) { alert('No results found'); return; }
                            const { lat, lon, display_name } = arr[0];
                            const latNum = parseFloat(lat), lonNum = parseFloat(lon);
                            if (Number.isFinite(latNum) && Number.isFinite(lonNum)){
                                if (searchMarker) { map.removeLayer(searchMarker); }
                                searchMarker = L.marker([latNum, lonNum]).addTo(map).bindPopup(display_name || q).openPopup();
                                map.setView([latNum, lonNum], 16);
                                setTimeout(()=>map.invalidateSize(), 100);
                            }
                        }catch(e){
                            console.error('Search error:', e);
                            alert('Search failed: '+(e && e.message ? e.message : 'Unknown error'));
                        }
                    }

                    btn && btn.addEventListener('click', performSearch);
                    input && input.addEventListener('keydown', function(ev){ if (ev.key === 'Enter'){ ev.preventDefault(); performSearch(); }});
                    clearBtn && clearBtn.addEventListener('click', function(){
                        if (searchMarker) { map.removeLayer(searchMarker); searchMarker = null; }
                        if (input) input.value = '';
                        map.setView(defaultCenter, defaultZoom);
                        setTimeout(()=>map.invalidateSize(), 100);
                    });
                    // --- End Search wiring ---

                    setTimeout(function(){ map.invalidateSize(); console.log('✅ Map size invalidated'); }, 100);
                    console.log('🎉 MAP WORKING IN CONTACT.PHP!');
                } catch (error) {
                    console.error('❌ Map creation error:', error);
                    console.error('Error stack:', error.stack);
                    showMapStatus(`Map failed: ${error.message}`, true);
                }
            }, 200);
        }

        document.addEventListener('DOMContentLoaded', function(){
            console.log('DOM loaded, starting map initialization...');
            // Small delay to ensure everything is ready
            setTimeout(function(){
                ensureLeaflet().then(()=>{
                    console.log('✅ Leaflet available, version:', L && L.version);
                    initMap();
                }).catch(err=>{
                    console.error('❌ Leaflet not available', err);
                    showMapStatus('Leaflet library not loaded. Check internet connection.', true);
                });
            }, 100);
        });
        </script>
    </main>

<?php include 'footer.php'; ?>
