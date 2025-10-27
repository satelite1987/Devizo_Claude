<?php
/**
 * DEVIZO v2.0 - Footer Template
 *
 * Common footer for all pages
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}
?>

</div> <!-- Close container -->

<!-- Footer -->
<footer style="background: white; margin-top: 50px; padding: 30px 0; border-top: 3px solid var(--primary); box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 20px; text-align: center; color: #666;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="text-align: left;">
                <strong style="color: var(--primary); font-size: 18px;">DEVIZO v2.0</strong>
                <p style="font-size: 12px; margin-top: 5px;">Modular Multi-Tenant SaaS Platform</p>
            </div>

            <div>
                <p style="font-size: 13px;">
                    &copy; <?php echo date('Y'); ?> DEVIZO - Toate drepturile rezervate
                </p>
                <p style="font-size: 11px; margin-top: 5px; opacity: 0.7;">
                    Powered by PHP <?php echo PHP_VERSION; ?> & MariaDB
                </p>
            </div>

            <?php if (DEBUG_MODE): ?>
                <div style="text-align: right;">
                    <p style="font-size: 11px; color: var(--warning);">
                        <i class="fas fa-exclamation-triangle"></i> <strong>DEBUG MODE ACTIV</strong>
                    </p>
                    <p style="font-size: 10px; margin-top: 3px;">
                        <?php
                        $time = microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
                        echo 'Page generated in ' . number_format($time * 1000, 2) . ' ms';
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

</body>
</html>
