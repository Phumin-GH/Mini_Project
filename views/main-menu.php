<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location:login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattaya Local Gems - ธุรกิจท้องถิ่นในพัทยา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
            <div class="col-md-6">
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
            <div class="col-md-2">
                <button class="btn btn-primary">เพิ่มหมุด</button>
            </div>
        </div>
        <div class="flex-full" style="min-width: 280px;">
            <div id="map" class="w-100 rounded-3 mt-2" style="height: 660px;"></div>
        </div>
        <!-- <div id="business-grid" class="row g-4">
        </div> -->
    </main>

    <footer class="text-center p-3 mt-5 bg-light">
        <p>&copy; 2025 Pattaya Local Gems</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
    $(document).ready(function() {
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
        function loadBusinesses(category = '', searchTerm = '') {
            $.ajax({
                url: 'api/get_locations.php.php', // ไฟล์ PHP ที่จะดึงข้อมูล
                type: 'GET',
                dataType: 'json',
                data: {
                    category: category,
                    search: searchTerm
                },
                beforeSend: function() {
                    // แสดงสถานะกำลังโหลด (Loading Spinner)
                    $('#business-grid').html(
                        '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                    );
                },
                success: function(data) {
                    $('#business-grid').empty(); // ล้างข้อมูลเก่าออกก่อน

                    if (data.length > 0) {
                        $.each(data, function(index, business) {
                            const businessCard = `
                                <div class="col-md-4">
                                    <div class="card h-100 business-card shadow-sm">
                                        <img src="${business.image}" class="card-img-top" alt="${business.name}">
                                        <div class="card-body">
                                            <h5 class="card-title">${business.name}</h5>
                                            <p class="card-text text-muted">${business.description}</p>
                                            <span class="badge bg-primary">${business.category}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#business-grid').append(businessCard);
                        });
                    } else {
                        $('#business-grid').html(
                            '<div class="col-12 text-center"><p>ไม่พบธุรกิจที่ตรงกับเงื่อนไข</p></div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error("เกิดข้อผิดพลาด: " + error);
                    $('#business-grid').html(
                        '<div class="col-12 text-center alert alert-danger"><p>ไม่สามารถโหลดข้อมูลได้</p></div>'
                    );
                }
            });
        }
        // เมื่อมีการเปลี่ยนแปลงในช่องค้นหา (พิมพ์เสร็จแล้วรอ 0.5 วินาทีค่อยค้นหา)
        let searchTimeout;
        $('#search-input').on('keyup', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val();
            const category = $('#category-filter').val();
            searchTimeout = setTimeout(function() {
                loadBusinesses(category, searchTerm);
            }, 500);
        });

        // เมื่อมีการเปลี่ยนแปลงในฟิลเตอร์ประเภทธุรกิจ
        $('#category-filter').on('change', function() {
            const category = $(this).val();
            const searchTerm = $('#search-input').val();
            loadBusinesses(category, searchTerm);
        });
        loadBusinesses();

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

    function loadAllMarkers() {
        $.ajax({
            url: 'get_locations.php',
            type: 'GET',
            dataType: 'json',
            success: function(locations) {
                // วนลูปสร้างหมุดจากข้อมูลที่ได้รับ
                locations.forEach(addMarker);
            },
            error: function(error) {
                console.error("ไม่สามารถโหลดข้อมูลหมุดได้:", error);
            }
        });
    }
    loadAllMarkers();
    const lat =
        <?php echo isset($house['Property_latitude']) ? floatval($house['Property_latitude']) : 13.7563; ?>;
    const lng =
        <?php echo isset($house['Property_longitude']) ? floatval($house['Property_longitude']) : 100.5018; ?>;
    const map = L.map('map').setView([lat, lng], 20);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map);
    </script>

</body>

</html>