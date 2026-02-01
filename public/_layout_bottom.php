<?php
declare(strict_types=1);
if (!empty($__logged_in)):
?>
    </main>
  </div>
</div>
<?php else: ?>
    </div>
  </main>
  <footer class="app-footer py-3">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span>© <?= date('Y') ?> <?= e(config()['app']['name']) ?>. All rights reserved.</span>
      <span class="text-secondary">HRMS · Attendance · Leave · Payroll</span>
    </div>
  </footer>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

