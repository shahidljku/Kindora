<?php require_once 'includes/header.php'; ?>



<?php
/**
 * PACKAGES PAGE - SINGLE PAGE DETAILS & BOOKING MODALS
 * - Details open in-page (modal) for any package
 * - Booking opens in-page (modal) for logged-in users; otherwise login modal
 * - Package data embedded as JSON for client-side rendering
 */

require_once __DIR__ . '/config.php';

// Fetch active packages (defensive)
$packages = [];
try {
    $packages = KindoraDatabase::query("SELECT * FROM travel_packages WHERE is_active = 1 ORDER BY package_id ASC") ?: [];
} catch (Exception $e) {
    error_log("PACKAGES ERROR: " . $e->getMessage());
}

// Aggregate stats
$packageStats = [];
try {
    $packageStats = KindoraDatabase::fetchOne("SELECT COUNT(*) as total, AVG(price) as avg_price, MIN(price) as min_price, MAX(price) as max_price FROM travel_packages WHERE is_active = 1") ?: ['total'=>0];
} catch (Exception $e) {
    $packageStats = ['total'=>0];
}

// Curated "more places" (small)
$morePlaces = [];
try {
    $morePlaces = KindoraDatabase::query(
        "SELECT destination_id, name, type, image_url, description 
         FROM destinations 
         WHERE is_active = 1 
           AND type NOT IN ('Continent', '7 wonders')
         ORDER BY featured DESC, destination_id DESC
         LIMIT 8"
    ) ?: [];
} catch (Exception $e) {
    // ignore - not fatal
}
// after your $morePlaces query, add:
$destinations_for_js = [];
foreach ($morePlaces as $d) {
    $destinations_for_js[] = [
        'destination_id' => intval($d['destination_id']),
        'name' => $d['name'] ?? '',
        'type' => $d['type'] ?? '',
        'description' => $d['description'] ?? '',
        'image_url' => $d['image_url'] ?? ''
    ];
}


// Prepare packages array for client-side (sanitized minimal fields)
$packages_for_js = [];
foreach ($packages as $p) {
    $packages_for_js[] = [
        'package_id' => intval($p['package_id']),
        'name' => $p['name'] ?? '',
        'description' => $p['description'] ?? '',
        'image_url' => $p['image_url'] ?? '',
        'price' => isset($p['price']) ? floatval($p['price']) : 0,
        'duration_days' => isset($p['duration_days']) ? intval($p['duration_days']) : (isset($p['duration']) ? intval($p['duration']) : 5),
        'features' => !empty($p['features']) ? array_values(array_filter(array_map('trim', explode(',', $p['features'])))) : ['Accommodation','Local Transport','Guided Tours'],
        'min_group_size' => intval($p['min_group_size'] ?? 2),
        'max_group_size' => intval($p['max_group_size'] ?? 15),
        'package_type' => $p['package_type'] ?? ($p['badge'] ?? 'standard'),
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Packages — Kindora (single-page details & booking)</title>
<?php echo linkCSS('common'); ?>
<style>
:root{
  --primary:#1e3c72; --accent:#ff6b35; --muted:#666; --bg:#f8f9fa;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Inter, 'Source Sans Pro', sans-serif;background:var(--bg);color:#222}
.container{max-width:1200px;margin:40px auto;padding:0 18px}
.header{background:linear-gradient(135deg,var(--primary),#2a5298);color:#fff;padding:44px 18px;border-radius:10px}
.header h1{font-size:2rem;margin-bottom:6px}
.header p{opacity:.95}
.stats{display:flex;gap:26px;margin-top:18px;flex-wrap:wrap}
.stat{ text-align:center }
.stat .n{font-size:1.6rem;font-weight:700}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-top:28px}
.card{background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.08);display:flex;flex-direction:column;transition:transform .18s}
.card:hover{transform:translateY(-6px)}
.thumb{height:200px;background:linear-gradient(135deg,var(--primary),#2a5298);display:flex;align-items:center;justify-content:center;position:relative}
.thumb img{width:100%;height:100%;object-fit:cover;display:block}
.badge{position:absolute;right:12px;top:12px;background:var(--accent);color:#fff;padding:8px 12px;border-radius:20px;font-weight:700}
.body{padding:16px;display:flex;flex-direction:column;gap:10px;flex:1}
.title{color:var(--primary);font-weight:700;font-size:1.05rem}
.desc{color:var(--muted);font-size:.95rem;flex:1}
.features{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.feature-pill{background:#f3f3f3;padding:6px 10px;border-radius:14px;font-size:.85rem;color:var(--muted)}
.row{display:flex;gap:10px;align-items:center}
.price{color:var(--accent);font-weight:800;font-size:1.3rem}
.actions{display:flex;gap:10px;margin-top:10px}
.btn{flex:1;padding:10px 12px;border-radius:8px;border:0;cursor:pointer;font-weight:700}
.btn.primary{background:var(--accent);color:#fff}
.btn.ghost{background:transparent;border:2px solid var(--primary);color:var(--primary)}
/* More places */
.more-places{margin-top:34px}
.pl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.place{background:#fff;padding:10px;border-radius:8px;display:flex;gap:10px;align-items:center;border:1px solid #eee}
.place .thumb{width:88px;height:64px;border-radius:6px;overflow:hidden;flex-shrink:0}
.place .thumb img{width:100%;height:100%;object-fit:cover}
/* modals */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:900;display:none}
.modal-overlay.show{display:block}
.modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;width:92%;max-width:760px;border-radius:10px;padding:18px;z-index:1000;display:none}
.modal.show{display:block}
.modal .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.modal .modal-body{max-height:60vh;overflow:auto;padding-right:6px}
.close{background:#eee;border-radius:6px;padding:6px 8px;cursor:pointer}
/* booking form */
.form-row{display:flex;gap:10px;margin-bottom:10px}
.form-row input, .form-row select, textarea{flex:1;padding:10px;border-radius:6px;border:1px solid #ddd}
textarea{min-height:100px;resize:vertical}
/* responsive */
@media(max-width:720px){.grid{grid-template-columns:1fr}.form-row{flex-direction:column}}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container">
  <div class="header">
    <h1>🎒 Travel Packages</h1>
    <p>Open details and book without leaving this page.</p>
    <div class="stats">
      <div class="stat"><div class="n"><?php echo intval($packageStats['total'] ?? 0); ?></div><div class="label">Packages</div></div>
      <div class="stat"><div class="n">₹<?php echo isset($packageStats['min_price']) ? number_format($packageStats['min_price'], 0) : 0; ?></div><div class="label">From</div></div>
      <div class="stat"><div class="n">4.8★</div><div class="label">Avg rating</div></div>
    </div>
  </div>

  <!-- Packages grid -->
  <div class="grid" id="packagesGrid">
    <?php if (count($packages) === 0): ?>
      <div style="padding:30px;background:#fff;border-radius:8px">No packages found.</div>
    <?php else: foreach ($packages as $pkg): 
        $pid = intval($pkg['package_id']);
        $pname = $pkg['name'] ?? 'Package';
        $pimg = !empty($pkg['image_url']) ? $pkg['image_url'] : '';
        $ptype = $pkg['package_type'] ?? ($pkg['badge'] ?? 'Standard');
        $price = isset($pkg['price']) ? number_format($pkg['price'], 0) : '0';
        $duration = isset($pkg['duration_days']) ? intval($pkg['duration_days']) : (isset($pkg['duration']) ? intval($pkg['duration']) : 5);
        $features = !empty($pkg['features']) ? array_filter(array_map('trim', explode(',', $pkg['features']))) : ['Accommodation','Transport'];
    ?>
      <div class="card" data-pid="<?php echo $pid; ?>">
        <div class="thumb">
          <?php if ($pimg): ?>
            <img src="<?php echo htmlspecialchars($pimg); ?>" alt="<?php echo htmlspecialchars($pname); ?>">
          <?php else: ?>
            <div style="font-size:40px;color:rgba(255,255,255,0.9)">🏨</div>
          <?php endif; ?>
          <div class="badge"><?php echo htmlspecialchars(ucfirst($ptype)); ?></div>
        </div>
        <div class="body">
          <div class="title"><?php echo htmlspecialchars($pname); ?></div>
          <div class="desc"><?php echo htmlspecialchars(mb_substr($pkg['description'] ?? '', 0, 120)); ?>...</div>
          <div class="features">
            <div class="feature-pill"><?php echo $duration; ?> days</div>
            <div class="feature-pill">₹<?php echo $price; ?> / person</div>
            <?php foreach (array_slice($features,0,2) as $f): ?>
              <div class="feature-pill"><?php echo htmlspecialchars($f); ?></div>
            <?php endforeach; ?>
          </div>

          <div class="actions">
            <button class="btn primary" onclick="openBookingModal(<?php echo $pid; ?>)">✈️ Book Now</button>
            <button class="btn ghost" onclick="openDetailsModal(<?php echo $pid; ?>)">Details →</button>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- More places -->
  <?php if (count($morePlaces) > 0): ?>
  <div class="more-places">
    <h3 style="margin:18px 0;color:var(--primary)">✦ More Places</h3>
    <div class="pl-grid">
      <?php foreach ($morePlaces as $pl): ?>
        <a class="place" href="javascript:void(0)" onclick="openPlacePreview(<?php echo intval($pl['destination_id']); ?>)">
          <div class="thumb">
            <?php if (!empty($pl['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($pl['image_url']); ?>" alt="<?php echo htmlspecialchars($pl['name']); ?>">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary),#2a5298);color:#fff">📍</div>
            <?php endif; ?>
          </div>
          <div>
            <div style="font-weight:700;color:var(--primary)"><?php echo htmlspecialchars($pl['name']); ?></div>
            <div style="color:var(--muted);font-size:.9rem"><?php echo htmlspecialchars(ucfirst($pl['type'] ?? '')); ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- OVERLAYS & MODALS -->

<div class="modal-overlay" id="globalOverlay"></div>

<!-- Details Modal -->
<div class="modal" id="detailsModal" role="dialog" aria-hidden="true">
  <div class="modal-head">
    <div style="display:flex;gap:12px;align-items:center">
      <h3 id="detailsTitle" style="margin:0"></h3>
      <div id="detailsType" style="color:var(--muted);font-weight:700"></div>
    </div>
    <div class="close" onclick="closeModal('detailsModal')">✕</div>
  </div>
  <div class="modal-body" id="detailsBody">
    <div style="display:flex;gap:14px;align-items:flex-start">
      <div style="flex:1;min-width:160px">
        <div id="detailsImage" style="width:100%;height:180px;border-radius:8px;overflow:hidden;background:#ddd"></div>
      </div>
      <div style="flex:2">
        <div id="detailsDesc" style="color:var(--muted);margin-bottom:12px"></div>
        <div id="detailsFeatures" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px"></div>
        <div style="margin-top:8px">
          <span style="font-weight:800;color:var(--accent);font-size:1.2rem" id="detailsPrice"></span>
          <span style="color:var(--muted);margin-left:8px">per person</span>
        </div>
      </div>
    </div>

    <div style="margin-top:14px;display:flex;gap:10px">
      <button class="btn primary" id="detailsBookBtn" onclick="">Book Now</button>
      <button class="btn ghost" onclick="closeModal('detailsModal')">Close</button>
    </div>
  </div>
</div>

<!-- Booking Modal -->
<div class="modal" id="bookingModal" aria-hidden="true">
  <div class="modal-head">
    <h3 id="bookingTitle">Book Package</h3>
    <div class="close" onclick="closeModal('bookingModal')">✕</div>
  </div>
  <div class="modal-body">
    <!-- If user not logged in, we'll replace form with login prompt dynamically -->
    <div id="bookingContent">
      <form id="bookingForm" method="POST" action="booking.php">
        <input type="hidden" name="package_id" id="form_package_id" value="">
        <input type="hidden" name="package_name" id="form_package_name" value="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? $_SESSION['csrf_token'] = bin2hex(random_bytes(24))); ?>">
<input type="hidden" name="destination_id" id="form_destination_id" value="">

        <div class="form-row">
          <input type="text" name="full_name" id="full_name" placeholder="Full name" required>
          <input type="email" name="email" id="email" placeholder="Email" required>
        </div>
        <div class="form-row">
          <input type="tel" name="phone" id="phone" placeholder="Phone (optional)">
          <input type="date" name="travel_date" id="travel_date" required>
        </div>
        <div style="margin-bottom:10px">
          <select name="package_option" id="package_option" required>
            <option value="">Choose Option</option>
            <option value="economy">Economy</option>
            <option value="standard">Standard</option>
            <option value="luxury">Luxury</option>
          </select>
        </div>
        <div style="margin-bottom:12px">
          <select name="guests" required>
            <?php for ($i=1;$i<=10;$i++): ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?> guest<?php echo $i>1?'s':''; ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div style="margin-bottom:12px">
          <textarea name="notes" placeholder="Special requests (optional)"></textarea>
        </div>

        <div style="display:flex;gap:10px">
          <button type="submit" class="btn primary">Confirm Booking</button>
          <button type="button" class="btn ghost" onclick="closeModal('bookingModal')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Login required modal (for quick login) -->
<div class="modal" id="loginRequiredModal" aria-hidden="true">
  <div class="modal-head">
    <h3>Login required</h3>
    <div class="close" onclick="closeModal('loginRequiredModal')">✕</div>
  </div>
  <div class="modal-body">
    <p style="color:var(--muted)">You need to be logged in to book. <a href="login.php?redirect=packages.php">Go to login</a> or <a href="register.php">register</a>.</p>
    <div style="display:flex;gap:10px;margin-top:12px">
      <a class="btn primary" href="login.php?redirect=packages.php">Login</a>
      <button class="btn ghost" onclick="closeModal('loginRequiredModal')">Cancel</button>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
/*
 Client-side logic:
 - packagesData: JSON of packages (created server-side)
 - openDetailsModal(id): fills details modal and shows it
 - openBookingModal(id): if logged in -> show booking modal populated; else show login modal
*/

// embed packages data
const packagesData = <?php echo json_encode($packages_for_js, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
const destinationsData = <?php echo json_encode($destinations_for_js, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

// helper: find package by id
function findPackageById(id){
  for (let i=0;i<packagesData.length;i++){
    if (parseInt(packagesData[i].package_id) === parseInt(id)) return packagesData[i];
  }
  return null;
}

function showOverlay(show=true){
  const ov = document.getElementById('globalOverlay');
  if (show) ov.classList.add('show'); else ov.classList.remove('show');
  ov.style.display = show ? 'block' : 'none';
  if (show) ov.addEventListener('click', () => { closeAllModals(); }, { once: true });
}

function openDetailsModal(id){
  const pkg = findPackageById(id);
  if (!pkg) return alert('Package not found');
  document.getElementById('detailsTitle').textContent = pkg.name;
  document.getElementById('detailsType').textContent = ' ' + (pkg.package_type || '');
  // image
  const imgWrap = document.getElementById('detailsImage');
  imgWrap.innerHTML = '';
  if (pkg.image_url){
    const img = document.createElement('img');
    img.src = pkg.image_url;
    img.alt = pkg.name;
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';
    imgWrap.appendChild(img);
  } else {
    imgWrap.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary),#2a5298);color:#fff">🏨</div>';
  }
  document.getElementById('detailsDesc').textContent = pkg.description || '';
  // features
  const featWrap = document.getElementById('detailsFeatures');
  featWrap.innerHTML = '';
  (pkg.features || []).forEach(f => {
    const el = document.createElement('div');
    el.className = 'feature-pill';
    el.textContent = f;
    featWrap.appendChild(el);
  });
  document.getElementById('detailsPrice').textContent = '₹' + (pkg.price ? parseInt(pkg.price) : '0');

  // Book button action: open booking modal with this package
  const bookBtn = document.getElementById('detailsBookBtn');
  bookBtn.onclick = function(){ openBookingModal(pkg.package_id); };

  // show
  document.getElementById('detailsModal').classList.add('show');
  showOverlay(true);
}

function openBookingModal(id){
  const pkg = findPackageById(id);
  // if package missing still allow booking form with id prefilled
  const pid = pkg ? pkg.package_id : id;
  const pname = pkg ? pkg.name : '';

  // server-side checks: the PHP below writes whether user is logged in
  const isLoggedIn = <?php echo (function_exists('isUserLoggedIn') && isUserLoggedIn()) ? 'true' : 'false'; ?>;

  if (!isLoggedIn){
    // show login prompt modal
    document.getElementById('loginRequiredModal').classList.add('show');
    showOverlay(true);
    return;
  }

  // populate form fields
  document.getElementById('form_package_id').value = pid;
  document.getElementById('form_package_name').value = pname;
  // set modal title
  document.getElementById('bookingTitle').textContent = 'Book — ' + (pname || 'Package');
  // open modal
  document.getElementById('bookingModal').classList.add('show');
  showOverlay(true);

  // Focus first field
  setTimeout(()=>{ const f = document.getElementById('full_name'); if(f)f.focus(); }, 150);
}

function closeModal(id){
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
  // hide overlay if no other modal visible
  setTimeout(()=> {
    const any = document.querySelectorAll('.modal.show');
    if (any.length === 0) showOverlay(false);
  }, 60);
}

function closeAllModals(){
  document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));
  showOverlay(false);
}

// small helper for "More Places" preview - if you want to wire more destination details later
function openPlacePreview(destId){
  const dest = (window.destinationsData || []).find(d => parseInt(d.destination_id) === parseInt(destId));
  if (!dest) {
    alert('Destination not found');
    return;
  }

  // Fill details modal with destination details
  document.getElementById('detailsTitle').textContent = dest.name;
  document.getElementById('detailsType').textContent = ' ' + (dest.type || '');
  const imgWrap = document.getElementById('detailsImage');
  imgWrap.innerHTML = '';
  if (dest.image_url) {
    const img = document.createElement('img');
    img.src = dest.image_url;
    img.alt = dest.name;
    img.style.width = '100%'; img.style.height = '100%'; img.style.objectFit = 'cover';
    imgWrap.appendChild(img);
  } else {
    imgWrap.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary),#2a5298);color:#fff">📍</div>';
  }
  document.getElementById('detailsDesc').textContent = dest.description || '';

  // features: show simple pills
  const featWrap = document.getElementById('detailsFeatures');
  featWrap.innerHTML = '';
  ['Popular place', dest.type || ''].forEach(f => {
    const el = document.createElement('div');
    el.className = 'feature-pill';
    el.textContent = f;
    featWrap.appendChild(el);
  });

  // price: attempt to fetch pricing from destination_pricing client-side (not available) — leave blank or show "Check price"
  document.getElementById('detailsPrice').textContent = 'Check price';

  // set Book Now to open booking modal with destination id
  const bookBtn = document.getElementById('detailsBookBtn');
  bookBtn.onclick = function(){
    // Populate booking form so booking.php receives destination_id
    document.getElementById('form_package_id').value = ''; // none
    // ensure there's a hidden destination field - if not present, create one
    let destInp = document.querySelector('input[name="destination_id"]');
    if (!destInp) {
      destInp = document.createElement('input');
      destInp.type = 'hidden';
      destInp.name = 'destination_id';
      destInp.id = 'form_destination_id';
      document.getElementById('bookingForm').appendChild(destInp);
    }
    destInp.value = dest.destination_id;

    // prefill package_name field so admin can see reference
    document.getElementById('form_package_name').value = dest.name;

    // open booking modal (same modal used for packages)
    openBookingModal(dest.destination_id);
  };

  // show modal
  document.getElementById('detailsModal').classList.add('show');
  showOverlay(true);
}


// close overlay on ESC
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') closeAllModals();
});
</script>

</body>
</html>
