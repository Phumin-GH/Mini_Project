<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['email'] ?? '';
require_once __DIR__ . "/../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Local Gems - ธุรกิจท้องถิ่นในประเทศไทย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        #map-main,
        #map-modal-edit,
        #map-modal-add {
            height: 400px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .mapboxgl-popup-content {
            max-width: 500px;
            width: 100%;
        }

        .custom-marker {
            background-size: cover;
            background-position: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            cursor: pointer;
        }

        .user-marker {
            border-color: #3498db;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
            }
        }

        .modal-custom-width .modal-dialog {
            max-width: 90vh !important;
        }

        .modal-custom-width .modal-body {
            overflow-x: auto !important;
        }
    </style>
</head>

<body>
    <header
        class="bg-primary text-white text-center p-4 shadow-sm d-flex justify-content-center align-items-center position-relative">
        <div>
            <h1>💎 Search Local Gems</h1>
            <p class="lead mb-0">ค้นพบเสน่ห์ของธุรกิจท้องถิ่นที่ซ่อนอยู่ในประเทศไทย</p>
        </div>
        <button class="btn btn-danger position-absolute top-0 end-0 m-3" id="logout-btn">ออกจากระบบ</button>
    </header>
    <main class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-7">
                <input type="text" id="search-input" class="form-control" placeholder="ค้นหาตามชื่อธุรกิจ...">
            </div>
            <div class="col-md-4">
                <select id="category-filter" class="form-select">
                    <option value="">ทุกประเภท</option>
                    <option value="restaurants">ร้านอาหาร</option>
                    <option value="cafe">คาเฟ่</option>
                    <option value="tourist">ที่เที่ยว</option>
                    <option value="bagery">เบเกอรี่</option>
                    <option value="fast_food">ฟาสต์ฟู้ด</option>
                    <option value="street_food">อาหารริมทาง</option>
                    <option value="souvenirs">ของฝาก</option>
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal"><i
                        class="fa-solid fa-map-pin"></i> เพิ่ม</button>
            </div>

        </div>
        <div class="flex-full" style="min-width: 280px;">
            <div id="map-main" class="w-100 rounded-3 mt-2 row g-4 " style="height: 660px;"></div>
        </div>
        <div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-custom-width">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLocationModalLabel">
                            <i class="fa-solid fa-map-pin"></i> เพิ่มตำแหน่งใหม่
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="locationForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="locationName" class="form-label">ชื่อสถานที่</label>
                                <input type="text" class="form-control" id="locationName" name="locationName" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">ประเภท</label>
                                <select name="category" class="form-select" aria-label="Default select example">
                                    <option value="">ทุกประเภท</option>
                                    <option value="restaurants">ร้านอาหาร</option>
                                    <option value="cafe">คาเฟ่</option>
                                    <option value="tourist">ที่เที่ยว</option>
                                    <option value="bagery">เบเกอรี่</option>
                                    <option value="fast_food">ฟาสต์ฟู้ด</option>
                                    <option value="street_food">อาหารริมทาง</option>
                                    <option value="souvenirs">ของฝาก</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">คำอธิบาย</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="locationImages" class="form-label">อัปโหลดรูปภาพ (เลือกได้หลายรูป)</label>
                                <input class="form-control" type="file" id="locationImages" name="locationImages[]"
                                    accept="image/*">
                                <div id="imagePreviewContainer" class="mt-2"
                                    style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                            </div>
                            <p>คลิกบนแผนที่เพื่อเลือกตำแหน่ง</p>
                            <div id="map-modal-add"></div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">ละติจูด</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">ลองจิจูด</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-primary" id="saveLocationBtn">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-custom-width">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editLocationModalLabel">
                            <i class="fa-solid fa-map-pin"></i> แก้ไขตำแหน่งใหม่
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="EditlocationForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <input type="hidden" class="form-control" id="EditlocationId" name="EditlocationId"
                                    required>
                                <label for="EditlocationName" class="form-label">ชื่อสถานที่</label>
                                <input type="text" class="form-control" id="EditlocationName" name="EditlocationName"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="EditCategory" class="form-label">ประเภท</label>
                                <select name="EditCategory" id="EditCategory" class="form-select"
                                    aria-label="Default select example">
                                    <option value="">ทุกประเภท</option>
                                    <option value="restaurants">ร้านอาหาร</option>
                                    <option value="cafe">คาเฟ่</option>
                                    <option value="tourist">ที่เที่ยว</option>
                                    <option value="bagery">เบเกอรี่</option>
                                    <option value="fast_food">ฟาสต์ฟู้ด</option>
                                    <option value="street_food">อาหารริมทาง</option>
                                    <option value="souvenirs">ของฝาก</option>
                                </select>
                            </div>
                            <p>คลิกบนแผนที่เพื่อเลือกตำแหน่ง</p>
                            <div id="map-modal-edit"></div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="Editlatitude" class="form-label">ละติจูด</label>
                                    <input type="text" class="form-control" id="Editlatitude" name="Editlatitude"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="Editlongitude" class="form-label">ลองจิจูด</label>
                                    <input type="text" class="form-control" id="Editlongitude" name="Editlongitude"
                                        readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="Editdescription" class="form-label">คำอธิบาย</label>
                                <textarea class="form-control" id="Editdescription" name="Editdescription"
                                    rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="EditlocationImages" class="form-label">อัปโหลดรูปภาพ
                                </label>
                                <input class="form-control" type="file" id="EditlocationImages"
                                    name="EditlocationImages[]" accept="image/*">
                                <div id="EditimagePreviewContainer" class="mt-2"
                                    style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-primary" id="EditLocationBtn">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center p-3 mt-5 bg-light">
        <p>&copy; 2025 Search Local Gems</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js'></script>
    <script>
        $(document).ready(function() {
            $('#locationImages').on('change', function(e) {
                const previewContainer = $('#imagePreviewContainer');
                previewContainer.empty();
                if (this.files && this.files.length > 0) {
                    $.each(this.files, function(index, file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgElement = $('<img />').attr('src', e.target.result).css({
                                'width': '100px',
                                'height': '100px',
                                'object-fit': 'cover',
                                'border-radius': '5px'
                            });
                            previewContainer.append(imgElement);
                        }
                        reader.readAsDataURL(file);
                    })
                }
            })
            $('#logout-btn').on('click', function() {
                if (confirm('คุณต้องการออกจากระบบใช่หรือไม่?')) {
                    $.ajax({
                        url: '../controls/logout.php',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                window.location.href = '../index.php';
                            }
                        },
                        error(xhr, status, error) {
                            console.error('Error :' + error);
                            alert('เกิดข้อผิดพลาดในการออกจากระบบ');
                        }
                    });
                }
            });
            $('#map-main').on('click', '.btn-edit', function() {
                const locationId = $(this).data('id');
                $.ajax({
                    url: '../api/get_locations.php',
                    dataType: 'json',
                    type: 'GET',
                    data: {
                        action: 'edit',
                        id: locationId
                    },
                    success: function(data) {
                        console.log(data);
                        $('#EditlocationId').val(data.location_id);
                        $('#EditlocationName').val(data.location_name);
                        $('#Editdescription').val(data.location_description);
                        $('#Editlongitude').val(data.lng);
                        $('#Editlatitude').val(data.lat);
                        $('#EditCategory').val(data.category);
                        const lat = data.lat;
                        const lng = data.lng;


                        const popup = new mapboxgl.Popup({
                                offset: 25
                            })
                            .setHTML(`<h3>${data.location_name}</h3>`);
                        new mapboxgl.Marker()
                            .setLngLat([lng, lat])
                            .setPopup(popup)
                            .addTo(modaleditMap);
                        $('#editLocationModal').modal('show');
                    }
                });
            });
            $('#map-main').on('click', '.btn-delete', function() {
                const locationId = $(this).data('id');
                if (!confirm('ต้องการลบหมุดสถานที่นี้ใช่ไหม?')) {
                    return;
                }
                $.ajax({
                    url: '../controls/manage_locations.php',
                    data: {
                        action: 'delete',
                        id: locationId
                    },
                    dataType: 'json',
                    type: 'POST',
                    success: function(res) {
                        console.log(res);
                        if (res.success === true) {
                            alert('ลบหมุดสำเร็จ');
                            window.location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
            $('#main-map').on('click', '.btn-open-map', function() {
                const lat = $(this).data('lat');
                const lng = $(this).data('lng');
                const googleMapsUrl = `https://www.google.com/maps?daddr=${lat},${lng}`;
                window.open(googleMapsUrl, '_blank');
            });
            $('#map-main').on('click', '.btn-map', function() {

                const destinationLng = $(this).data('lng');
                const destinationLat = $(this).data('lat');
                const locationName = $(this).closest('.mapboxgl-popup-content').find('h3').text().split(
                    '|')[0].trim();

                if (confirm('ต้องการแสดงเส้นทางมายัง "' + locationName + '" ใช่หรือไม่?')) {

                    showRouteOnMapbox([destinationLng, destinationLat], locationName);
                }
            });

            function showRouteOnMapbox(destinationCoords, locationName) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const startCoords = [position.coords.longitude, position.coords.latitude];
                        const apiUrl =
                            `https://api.mapbox.com/directions/v5/mapbox/driving/${startCoords[0]},${startCoords[1]};${destinationCoords[0]},${destinationCoords[1]}?steps=true&geometries=geojson&access_token=${mapboxgl.accessToken}`;

                        $.ajax({
                            url: apiUrl,
                            type: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                const routeCoordinates = data.routes[0].geometry.coordinates;

                                const routeGeoJSON = {
                                    'type': 'Feature',
                                    'properties': {},
                                    'geometry': {
                                        'type': 'LineString',
                                        'coordinates': routeCoordinates
                                    }
                                };
                                if (mainMap.getSource('route')) {
                                    mainMap.getSource('route').setData(routeGeoJSON);
                                } else {
                                    mainMap.addLayer({
                                        'id': 'route',
                                        'type': 'line',
                                        'source': {
                                            'type': 'geojson',
                                            'data': routeGeoJSON
                                        },
                                        'layout': {
                                            'line-join': 'round',
                                            'line-cap': 'round'
                                        },
                                        'paint': {
                                            'line-color': '#3887be',
                                            'line-width': 5,
                                            'line-opacity': 0.75
                                        }
                                    });
                                }
                                const bounds = new mapboxgl.LngLatBounds(startCoords,
                                    destinationCoords);
                                mainMap.fitBounds(bounds, {
                                    padding: 80
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching directions:', error);
                                alert('ไม่สามารถคำนวณเส้นทางได้');
                            }
                        });

                    },
                    function(error) {
                        console.error('Error getting location:', error);
                        alert('ไม่สามารถเข้าถึงตำแหน่งปัจจุบันของคุณได้');
                    },

                    {
                        enableHighAccuracy: true
                    }
                );
            }
            // เมื่อมีการเปลี่ยนแปลงในช่องค้นหา (พิมพ์เสร็จแล้วรอ 0.5 วินาทีค่อยค้นหา)
            let searchTimeout;
            $('#search-input').on('keyup', function() {
                clearTimeout(searchTimeout);
                const searchTerm = $(this).val();
                const category = $('#category-filter').val();
                searchTimeout = setTimeout(function() {
                    loadAllMarkers(category, searchTerm);
                }, 500);
            });
            $('#category-filter').on('change', function() {
                const category = $(this).val();
                const searchTerm = $('#search-input').val();
                loadAllMarkers(category, searchTerm);
            });


        });

        $('#locationForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');
            console.log(formData);
            $.ajax({
                url: '../controls/manage_locations.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.success === true) {
                        alert('สำเร็จ' + data.message);
                        $('#addLocationModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            })
        });
        $('#EditlocationForm').on('submit', function(e) {
            e.preventDefault();
            const formEditData = new FormData(this);
            formEditData.append('action', 'edit');
            console.log(formEditData);
            $.ajax({
                url: '../controls/manage_locations.php',
                type: 'POST',
                data: formEditData,
                processData: false,
                contentType: false,
                dataType: 'json',

                success: function(data) {
                    console.log(data);
                    // const data = JSON.parse(datat);
                    if (data.success === true) {
                        alert('แก้ไขสำเร็จ: ' + data.message);
                        $('#editLocationModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('เกิดข้อผิดพลาดในการแก้ไข');
                }
            })
        });


        mapboxgl.accessToken = '<?= $_ENV['MapBox_key'] ?>';
        const mainMap = new mapboxgl.Map({
            container: 'map-main',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [100.8825, 12.9236],
            zoom: 9
        });
        mainMap.addControl(new mapboxgl.NavigationControl());
        let markers = [];

        function loadAllMarkers(category = '', searchTerm = '') {
            if (markers.length > 0) {
                markers.forEach(marker => marker.remove());
                markers = [];
            }
            $.ajax({
                url: '../api/get_locations.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    category: category,
                    search: searchTerm,
                    action: 'search'
                },
                success: function(locations) {
                    console.log(locations);
                    const currentUser = <?php echo json_encode($current_user_id); ?>;
                    if (locations && locations.length > 0) {
                        locations.forEach(function(location) {
                            const el = document.createElement('div');
                            el.className = 'custom-marker';
                            const lat = location.lat;
                            const lng = location.lng;
                            const name = location.location_name;
                            const des = location.location_description;
                            const image = location.img_name;
                            const category = location.category;
                            const lct_id = location.location_id;
                            const username = location.username;
                            let role = location.user_role;
                            if (role === 'General_user') {
                                role = 'ผู้ใช้งานทั่วไป';
                            } else {
                                role = 'เจ้าของกิจการ';

                            }
                            el.style.backgroundImage = `url('../${image}')`;
                            if (lat && lng) {

                                if (location.email == currentUser) {
                                    el.classList.add('user-marker');
                                    el.style.borderColor = '#3498db'; // เปลี่ยนเป็นสีฟ้า
                                } else {
                                    el.style.borderColor = '#95a5a6';
                                }
                                if (location.email === currentUser) {
                                    buttonHtml = `
            <div class="mt-2 text-start d-flex gap-2">
                <button class="btn btn-sm btn-primary btn-edit" data-id="${location.location_id}" data-bs-toggle="modal" data-bs-target="#editLocationModal">
                    <i class="fas fa-pencil-alt"></i> แก้ไข
                </button>
                <button class="btn btn-sm btn-danger btn-delete " data-id="${location.location_id}" >
                    <i class="fas fa-trash"></i> ลบ
                </button>
                <button class="btn btn-sm btn-success btn-map" data-id="${location.location_id}" data-lat="${location.lat}" data-lng="${location.lng}" >
                    <i class="fas fa-trash"></i> นำทาง
                </button>
                
            </div>
        `;
                                } else {
                                    buttonHtml = `<button class="btn btn-sm btn-success btn-map" data-id="${location.location_id}" data-lat="${location.lat}" data-lng="${location.lng}" >
                    <i class="fas fa-trash"></i> นำทาง
                </button>`;
                                }
                                const popupHtml = `
                                    <h3>${name} | ${category} |${lct_id}</h3>
                                    <img 
                                        src="../${image}" 
                                        alt="${name} " style="width: 100%; max-width: 300px; height: auto; display: block; margin: 0 auto; border-radius:5px;"
                                        class="popup-image"
                                    >
                                    <h4>${username} สถานะ ${role}</h4>
                                    <p style="font-size:18px;">${des}</p>
                                    ${buttonHtml} 
                                `;
                                const popup = new mapboxgl.Popup({
                                        offset: 25,
                                        maxWidth: '400px'
                                    })
                                    .setHTML(popupHtml);
                                // new mapboxgl.Marker(el)
                                //     .setLngLat([lng, lat])
                                //     .setPopup(popup)
                                //     .addTo(mainMap);
                                const newMarker = new mapboxgl.Marker(el)
                                    .setLngLat([lng, lat])
                                    .setPopup(popup)
                                    .addTo(mainMap);
                                markers.push(newMarker);

                            }

                        });

                    } else {
                        console.log("ไม่พบข้อมูลตำแหน่ง");
                    }
                },
                error: function(error) {
                    console.error("ไม่สามารถโหลดข้อมูลหมุดได้:", error);
                }
            });
        }
        mainMap.on('load', function() {
            loadAllMarkers();
        });
        const addLocationModal = document.getElementById('addLocationModal');
        let modalMap = null;
        let marker = null;
        addLocationModal.addEventListener('show.bs.modal', function() {
            if (!modalMap) {
                modalMap = new mapboxgl.Map({
                    container: 'map-modal-add',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [100.8825, 12.9236],
                    zoom: 8
                });
                modalMap.addControl(new mapboxgl.NavigationControl());
                modalMap.on('click', function(e) {
                    const {
                        lng,
                        lat
                    } = e.lngLat;

                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);

                    if (marker) {
                        marker.setLngLat([lng, lat]);
                    } else {
                        marker = new mapboxgl.Marker().setLngLat([lng, lat]).addTo(modalMap);
                    }
                });
            }
            setTimeout(function() {
                modalMap.resize();
            }, 10);
        });
        const editLocationModal = document.getElementById('editLocationModal');
        let modaleditMap = null;
        let markeredit = null;
        editLocationModal.addEventListener('shown.bs.modal', function() {
            if (!modaleditMap) {

                modaleditMap = new mapboxgl.Map({
                    container: 'map-modal-edit',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [100.8825, 12.9236],
                    zoom: 8
                });
                moaleditMapap.addControl(new mapboxgl.NavigationControl());
                modaleditMap.on('click', function(e) {
                    const {
                        lng,
                        lat
                    } = e.lngLat;

                    document.getElementById('Editlatitude').value = lat.toFixed(6);
                    document.getElementById('Editlongitude').value = lng.toFixed(6);

                    if (markeredit) {
                        markeredit.setLngLat([lng, lat]);
                    } else {
                        markeredit = new mapboxgl.Marker().setLngLat([lng, lat]).addTo(modaleditMap);
                    }
                });
            }
            setTimeout(function() {
                modaleditMap.resize();
            }, 10);
        });
        loadAllMarkers();
        $('#addLocationModal').on('hidden.bs.modal', function() {
            $('#locationForm')[0].reset();
            $('#imagePreviewContainer').empty();
            $('#latitude').val('');
            $('#longitude').val('');
            if (marker) {
                marker.remove();
                marker = null;
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
        $('#editLocationModal').on('hidden.bs.modal', function() {
            $('#EditlocationForm')[0].reset();
            $('#EditimagePreviewContainer').empty();
            if (markeredit) {
                markeredit.remove();
                markeredit = null;
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        });
    </script>

</body>

</html>