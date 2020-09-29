            </div>
            </div>
            </div>
            <footer class="footer">
                <p><?php echo $copyright; ?></p>
            </footer>
    </div>
</div>
<?php if(!$disable_cron) { ?>
<noscript>
    <img src="<?php echo BASE_URL; ?>/cron.php?type=image" alt="" />
</noscript>
<?php } ?>
</body>
</html>