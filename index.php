<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hava Durumu Panosu</title>
    <style>
        :root {
            --primary-color:rgb(125, 62, 46);
            --secondary-color: #a5d6a7;
            --background-color: #f1f8e9;
            --text-color: #1b5e20;
            --card-background: #ffffff;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--background-color);
            color: var(--text-color);
        }

        header {
            background: var(--primary-color);
            color: white;
            padding: 1rem 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 10px 10px;
        }

        header h1 {
            font-size: 2rem;
            margin: 0;
        }

        header h2 {
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .weather-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            justify-items: center;
        }

        .weather-card {
            background: var(--card-background);
            border: 1px solid var(--secondary-color);
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .weather-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .details {
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--card-background);
            border: 1px solid var(--secondary-color);
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .details div {
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            border-radius: 10px 10px 0 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hava Durumu Panosu</h1>
        <h2 id="location"></h2>
    </header>
    <div class="container">
        <section class="weather-summary" id="weather-summary">
            <!-- Hava durumu özet kartları buraya eklenecek -->
        </section>
        <section class="details" id="detailed-info">
            <h2>Detaylı Hava Durumu Bilgisi</h2>
            <div id="temperature"></div>
            <div id="humidity"></div>
            <div id="pressure"></div>
            <div id="wind"></div>
            <div id="coordinates"></div>
        </section>
    </div>
    <footer>
        &copy; 2024 Powered by AGLSOFT
    </footer>
    <script>
        async function fetchWeatherData(lat, lon) {
            try {
                const openMeteoResponse = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&current_weather=true&timezone=Europe/Istanbul`);
                if (!openMeteoResponse.ok) {
                    throw new Error(`HTTP error! Status: ${openMeteoResponse.status}`);
                }
                const openMeteoData = await openMeteoResponse.json();

                const weatherApiResponse = await fetch(`https://api.weatherapi.com/v1/current.json?key=579a732290424a2f9d5150603240112&q=${lat},${lon}`);
                if (!weatherApiResponse.ok) {
                    throw new Error(`HTTP error! Status: ${weatherApiResponse.status}`);
                }
                const weatherApiData = await weatherApiResponse.json();

                return { openMeteoData, weatherApiData };
            } catch (error) {
                console.error("Hava durumu verileri alınamadı:", error);
                alert("Hava durumu verileri alınırken bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
                return null;
            }
        }

        function calculateAbsoluteHumidity(relativeHumidity, temperature) {
            const e = (6.112 * Math.exp((17.67 * temperature) / (temperature + 243.5)) * relativeHumidity) / 100;
            const absHumidity = (e * 2.1674) / (273.15 + temperature);
            return absHumidity.toFixed(2);
        }

        function displayWeatherSummary(data) {
            const summaryContainer = document.getElementById('weather-summary');
            summaryContainer.innerHTML = '';

            data.openMeteoData.daily.time.forEach((date, index) => {
                const card = document.createElement('div');
                card.classList.add('weather-card');
                const formattedDate = new Date(date).toLocaleDateString('tr-TR');
                card.innerHTML = `
                    <h3>${formattedDate}</h3>
                    <p>Max Sıcaklık: ${data.openMeteoData.daily.temperature_2m_max[index]}&deg;C</p>
                    <p>Min Sıcaklık: ${data.openMeteoData.daily.temperature_2m_min[index]}&deg;C</p>
                    <p>Yağış: ${data.openMeteoData.daily.precipitation_sum[index] || 0} mm</p>
                `;
                summaryContainer.appendChild(card);
            });
        }

        function displayDetailedInfo(data, lat, lon) {
            const today = data.openMeteoData.current_weather;
            const currentWeather = data.weatherApiData.current;

            const absHumidity = calculateAbsoluteHumidity(currentWeather.humidity, today.temperature);
            const pressureType = currentWeather.pressure_mb > 1013 ? 'Yüksek Basınç' : 'Alçak Basınç';

            document.getElementById('temperature').innerHTML = `Sıcaklık: ${today.temperature}&deg;C`;
            document.getElementById('humidity').innerHTML = `Nem: ${currentWeather.humidity}%<br>Mutlak Nem: ${absHumidity} g/m³`;
            document.getElementById('pressure').innerHTML = `Basınç: ${currentWeather.pressure_mb} hPa (${pressureType})`;
            document.getElementById('wind').innerHTML = `Rüzgar: ${today.windspeed} km/sa, Yön: ${today.winddirection}&deg;`;
            document.getElementById('coordinates').innerHTML = `Koordinatlar: Enlem ${lat}, Boylam ${lon}`;
        }

        async function displayLocationName(lat, lon) {
            try {
                const response = await fetch(`https://geocode.maps.co/reverse?lat=${lat}&lon=${lon}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const locationData = await response.json();
                if (locationData && locationData.address) {
                    const locationName = `${locationData.address.city || locationData.address.town || locationData.address.village}, ${locationData.address.country}`;
                    document.getElementById('location').textContent = `Konum: ${locationName}`;
                }
            } catch (error) {
                console.error("Konum bilgisi alınamadı:", error);
                document.getElementById('location').textContent = 'Konum: Bilgi alınamadı';
            }
        }

        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(async (position) => {
                    const { latitude, longitude } = position.coords;
                    const weatherData = await fetchWeatherData(latitude, longitude);
                    if (weatherData) {
                        displayWeatherSummary(weatherData);
                        displayDetailedInfo(weatherData, latitude, longitude);
                        displayLocationName(latitude, longitude);
                    }
                }, () => {
                    alert('Konumunuz alınamadı.');
                });
            } else {
                alert('Tarayıcınız konum bilgisi desteği sağlamıyor.');
            }
        }

        // Uygulamayı başlat
        getUserLocation();
    </script>
</body>
</html>
