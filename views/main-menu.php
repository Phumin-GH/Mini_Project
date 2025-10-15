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
    <title>Pattaya Local Gems - ธุรกิจท้องถิ่นในพัทยา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" /> -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        #map-main,
        #map-modal {
            height: 400px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
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

        /* สไตล์สำหรับหมุดของ "ตัวเอง" (สีฟ้า) */
        .user-marker {
            border-color: #3498db;
            /* อาจจะเพิ่ม animation ได้ */
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
    </style>
</head>

<body>

    <header
        class="bg-primary text-white text-center p-4 shadow-sm d-flex justify-content-center align-items-center position-relative">
        <div>
            <h1>💎 Pattaya Local Gems</h1>
            <p class="lead mb-0">ค้นพบเสน่ห์ของธุรกิจท้องถิ่นที่ซ่อนอยู่ในพัทยา</p>
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
            <div class="modal-dialog modal-dialog-centered">
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
                            <div id="map-modal"></div>

                            <!-- ส่วนของ Latitude/Longitude -->
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
    </main>

    <footer class="text-center p-3 mt-5 bg-light">
        <p>&copy; 2025 Pattaya Local Gems</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js'></script>
    <script>
        $(document).ready(function() {
            $('#locationImages').on('change', function(e) {
                const previewContainer = $('#imagePreviewContainer');
                previewContainer.empty();
                if (this.file && this.file.length > 0) {
                    $.each(this.file, function(index, file) {
                        const reader = new FileReader();
                        reder.onload = function(e) {
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
                        dataType: 'JSON',
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
            // --- ฟังก์ชันหลักสำหรับดึงและแสดงข้อมูล ---
            // function loadBusinesses(category = '', searchTerm = '') {
            //     $.ajax({
            //         url: 'api/get_locations.php', // ไฟล์ PHP ที่จะดึงข้อมูล
            //         type: 'GET',
            //         dataType: 'json',
            //         data: {
            //             category: category,
            //             search: searchTerm
            //         },
            //         beforeSend: function() {
            //             // แสดงสถานะกำลังโหลด (Loading Spinner)
            //             $('#business-grid').html(
            //                 '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            //             );
            //         },
            //         success: function(data) {
            //             $('#business-grid').empty(); // ล้างข้อมูลเก่าออกก่อน

            //             if (data.length > 0) {
            //                 $.each(data, function(index, business) {
            //                     const businessCard = `
            //                         <div class="col-md-4">
            //                             <div class="card h-100 business-card shadow-sm">
            //                                 <img src="${business.image}" class="card-img-top" alt="${business.name}">
            //                                 <div class="card-body">
            //                                     <h5 class="card-title">${business.name}</h5>
            //                                     <p class="card-text text-muted">${business.description}</p>
            //                                     <span class="badge bg-primary">${business.category}</span>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     `;
            //                     $('#business-grid').append(businessCard);
            //                 });
            //             } else {
            //                 $('#business-grid').html(
            //                     '<div class="col-12 text-center"><p>ไม่พบธุรกิจที่ตรงกับเงื่อนไข</p></div>'
            //                 );
            //             }
            //         },
            //         error: function(xhr, status, error) {
            //             console.error("เกิดข้อผิดพลาด: " + error);
            //             $('#business-grid').html(
            //                 '<div class="col-12 text-center alert alert-danger"><p>ไม่สามารถโหลดข้อมูลได้</p></div>'
            //             );
            //         }
            //     });
            // }
            // เมื่อมีการเปลี่ยนแปลงในช่องค้นหา (พิมพ์เสร็จแล้วรอ 0.5 วินาทีค่อยค้นหา)
            // let searchTimeout;
            // $('#search-input').on('keyup', function() {
            //     clearTimeout(searchTimeout);
            //     const searchTerm = $(this).val();
            //     const category = $('#category-filter').val();
            //     searchTimeout = setTimeout(function() {
            //         loadBusinesses(category, searchTerm);
            //     }, 500);
            // });

            // // เมื่อมีการเปลี่ยนแปลงในฟิลเตอร์ประเภทธุรกิจ
            // $('#category-filter').on('change', function() {
            //     const category = $(this).val();
            //     const searchTerm = $('#search-input').val();
            //     loadBusinesses(category, searchTerm);
            // });
            // loadBusinesses();

        });
        // mapboxgl.accessToken = '<?php /*echo $_ENV['MapBox_key']*/ ?>';

        // const map = new mapboxgl.Map({
        //     container: 'map',
        //     style: 'mapbox://styles/mapbox/streets-v11',
        //     center: [100.523186, 13.736717], // จุดเริ่มต้นที่กรุงเทพฯ
        //     zoom: 5
        // });

        // // สร้าง Object เพื่อเก็บ Marker ทั้งหมด, ใช้ id เป็น key
        // let markers = {};
        $('#locationForm').on('submit', function(e) {
            e.preventDefault();
            // let formLocation = $(this).serialize();
            // formLocation += '&action=add';
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
                // dataType: 'text',
                success: function(data) {
                    console.log(data);
                    // const data = JSON.parse(datat);
                    if (data.success === true) {
                        alert('สำเร็จ' + data.message);
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


        mapboxgl.accessToken = '<?= $_ENV['MapBox_key'] ?>';
        const mainMap = new mapboxgl.Map({
            container: 'map-main', // ID ของ div
            style: 'mapbox://styles/mapbox/streets-v12', // สไตล์แผนที่
            center: [100.8825, 12.9236], // [lng, lat]
            zoom: 9
        });

        function loadAllMarkers() {
            $.ajax({
                url: '../api/get_locations.php',
                type: 'GET',
                dataType: 'json',
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
                            if (lat && lng) {

                                if (location.email == currentUser) {
                                    el.classList.add('user-marker');
                                    el.style.backgroundColor = '#3498db'; // เปลี่ยนเป็นสีฟ้า
                                } else {
                                    el.style.backgroundColor = '#95a5a6';
                                }
                                const popupHtml = `
                                    <h3>${name}</h3>
                                    <img 
                                        src="${image}" 
                                        alt="${name}" 
                                        class="popup-image"
                                    >
                                    <p>${des}</p>
                                `;
                                const popup = new mapboxgl.Popup({
                                        offset: 25
                                    })
                                    .setHTML(popupHtml);
                                new mapboxgl.Marker(el)
                                    .setLngLat([lng, lat])
                                    .setPopup(popup)
                                    .addTo(mainMap);

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
        addLocationModal.addEventListener('shown.bs.modal', function() {
            if (!modalMap) {
                modalMap = new mapboxgl.Map({
                    container: 'map-modal',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [100.8825, 12.9236],
                    zoom: 8
                });

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

            // คำสั่งวิเศษสำหรับแผนที่ใน Modal (เทียบเท่า invalidateSize)
            setTimeout(function() {
                modalMap.resize();
            }, 10);
        });
        // เพิ่ม TileLayer (พื้นหลังแผนที่)
        // const map_main = L.map('map-main').setView([12.9236, 100.8825], 12);

        // L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        //     attribution: '&copy; OpenStreetMap contributors'
        // }).addTo(map_main);
        // const addModal = document.getElementById('addLocationModal');
        // let map_modal = null; // สร้างตัวแปรแผนที่ใน Modal ไว้ก่อน แต่ยังไม่สร้าง Object
        // let marker = null;
        // addModal.addEventListener('shown.bs.modal', function() {
        //     if (!map_modal) {
        //         map_modal = L.map('map-modal').setView([12.9236, 100.8825], 9);
        //         L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        //             attribution: '&copy; OpenStreetMap contributors'
        //         }).addTo(map_modal);
        //         map_modal.on('click', function(e) {
        //             const lat = e.latlng.lat;
        //             const lng = e.latlng.lng;
        //             document.getElementById('latitude').value = lat.toFixed(6);
        //             document.getElementById('longitude').value = lng.toFixed(6);
        //             if (marker) {
        //                 marker.setLatLng(e.latlng);
        //             } else {
        //                 marker = L.marker(e.latlng).addTo(map_modal);
        //             }

        //         });
        //         setTimeout(function() {
        //             map_modal.invalidateSize();
        //         }, 10);

        //     }
        // });
        loadAllMarkers();
    </script>

</body>

</html>