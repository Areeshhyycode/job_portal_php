<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Browse jobs';

// --- Filters ---
$q        = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$category = trim($_GET['category'] ?? '');
$type     = trim($_GET['type'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * PER_PAGE;

// --- Build WHERE dynamically with bound params ---
$where  = ["j.status = 'approved'"];
$params = [];
if ($q !== '') {
    $where[] = '(j.title LIKE ? OR j.description LIKE ? OR u.company_name LIKE ?)';
    $like = "%$q%";
    array_push($params, $like, $like, $like);
}
if ($location !== '') { $where[] = 'j.location LIKE ?'; $params[] = "%$location%"; }
if ($category !== '') { $where[] = 'j.category = ?';    $params[] = $category; }
if ($type !== '')     { $where[] = 'j.job_type = ?';    $params[] = $type; }
$whereSql = implode(' AND ', $where);

// --- Count for pagination ---
$countStmt = db()->prepare("SELECT COUNT(*) FROM jobs j JOIN users u ON u.id=j.company_id WHERE $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / PER_PAGE));

// --- Fetch page (LIMIT/OFFSET bound as ints) ---
$sql = "SELECT j.*, u.company_name
        FROM jobs j JOIN users u ON u.id = j.company_id
        WHERE $whereSql
        ORDER BY j.created_at DESC
        LIMIT $offset, " . PER_PAGE;
$stmt = db()->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$categories = ['Software', 'Design', 'Marketing', 'Sales', 'Finance', 'Support'];
$types = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'];

// Helper to preserve query string across pagination links
function page_link(int $p): string {
    $params = $_GET; $params['page'] = $p;
    return url('jobs/index.php') . '?' . http_build_query($params);
}
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-head">
      <h2>Browse jobs <span class="muted" style="font-size:1rem;font-weight:500">(<?= $total ?> found)</span></h2>
    </div>

    <form method="get" class="card card-pad" style="margin-bottom:24px">
      <div class="row">
        <div class="field-group"><label>Keyword</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Title, company…"></div>
        <div class="field-group"><label>Location</label><input class="input" name="location" value="<?= e($location) ?>" placeholder="City"></div>
      </div>
      <div class="row">
        <div class="field-group"><label>Category</label>
          <select class="input" name="category"><option value="">All</option>
            <?php foreach ($categories as $c): ?><option <?= $category===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field-group"><label>Job type</label>
          <select class="input" name="type"><option value="">All</option>
            <?php foreach ($types as $t): ?><option <?= $type===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-primary" type="submit">Apply filters</button>
        <a class="btn btn-ghost" href="<?= url('jobs/index.php') ?>">Reset</a>
      </div>
    </form>

    <?php if (!$jobs): ?>
      <div class="empty"><div class="ico">🔎</div>No jobs match your search. Try broadening your filters.</div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($jobs as $job) { include __DIR__ . '/../includes/job_card.php'; } ?>
      </div>

      <?php if ($pages > 1): ?>
      <div class="pagination">
        <a class="<?= $page<=1?'disabled':'' ?>" href="<?= page_link(max(1,$page-1)) ?>">← Prev</a>
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p == $page): ?><span class="active"><?= $p ?></span>
          <?php else: ?><a href="<?= page_link($p) ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
        <a class="<?= $page>=$pages?'disabled':'' ?>" href="<?= page_link(min($pages,$page+1)) ?>">Next →</a>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
