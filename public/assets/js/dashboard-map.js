(function () {
    function buildFilterUrl(district) {
        var url = new URL(window.location.href);

        if (district) {
            url.searchParams.set('kecamatan', district);
        } else {
            url.searchParams.delete('kecamatan');
        }

        return url.toString();
    }

    function createPopup(location) {
        var popup = document.createElement('div');
        var title = document.createElement('strong');
        var status = document.createElement('div');
        var district = document.createElement('div');
        var link = document.createElement('a');
        var filter = document.createElement('a');

        title.textContent = location.name || '-';
        status.textContent = location.status || '-';
        district.textContent = location.kecamatan || '';
        link.href = location.maps || '#';
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent = 'Buka Google Maps';
        filter.href = buildFilterUrl(location.kecamatan || '');
        filter.textContent = 'Filter kecamatan ini';
        filter.style.display = 'block';
        filter.style.marginTop = '6px';

        popup.appendChild(title);
        popup.appendChild(status);
        popup.appendChild(district);
        popup.appendChild(link);
        popup.appendChild(filter);

        return popup;
    }

    function readLocations() {
        var locationsElement = document.getElementById('company-map-locations');
        var encodedLocations = locationsElement ? locationsElement.textContent : '';

        if (!encodedLocations) {
            return [];
        }

        try {
            return JSON.parse(window.atob(encodedLocations));
        } catch (error) {
            return [];
        }
    }

    function getSelectedDistrict() {
        var element = document.getElementById('dashboard-selected-district');

        return element ? (element.value || '').trim() : '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var mapElement = document.getElementById('company-map');

        if (!mapElement || !window.L) {
            return;
        }

        var locations = readLocations();
        var map = L.map(mapElement).setView([-7.70, 112.75], 10);
        var bounds = L.latLngBounds([]);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        locations.forEach(function (location) {
            var marker = L.circleMarker([location.lat, location.lng], {
                color: '#ffffff',
                fillColor: location.color || '#4e73df',
                fillOpacity: 0.9,
                opacity: 1,
                radius: 4,
                weight: 1
            }).addTo(map);

            marker.bindPopup(createPopup(location));
            bounds.extend([location.lat, location.lng]);
        });

        if (bounds.isValid()) {
            map.fitBounds(bounds, {
                padding: [24, 24],
                maxZoom: 13
            });
        }
    });
})();
