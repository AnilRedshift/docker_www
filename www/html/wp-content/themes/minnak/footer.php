<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package MiNNaK
 */

?>
    <footer id="colophon" class="site-footer">
    <div id="site-info" class="site-info">
      <?php minnak_site_copyright(); ?>
      <span>
          Visitors:
          <?php
            echo do_shortcode('[koko_analytics_site_counter]');
          ?>
      </span>
      <?php minnak_credit(); ?>
    </div>
    </footer>
  </main>
</div>

<?php wp_footer(); ?>

</body>
</html>
