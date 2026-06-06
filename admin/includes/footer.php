        </div>
        <!-- / Layout container -->
    </div>
    <!-- / Layout wrapper -->

    <script src="<?php echo ADMIN_URL; ?>/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?php echo ADMIN_URL; ?>/assets/vendor/js/bootstrap.js"></script>
    
    <script>
    function toggleAdminMenu() {
        var layout = document.getElementById('admin-layout-wrapper');
        var isExpanded = layout.classList.contains('layout-menu-expanded');
        document.body.classList.toggle('admin-menu-open', !isExpanded);
        if (isExpanded) {
            layout.classList.remove('layout-menu-expanded');
        } else {
            layout.classList.add('layout-menu-expanded');
        }
    }

    function closeAdminMenu() {
        var layout = document.getElementById('admin-layout-wrapper');
        layout.classList.remove('layout-menu-expanded');
        document.body.classList.remove('admin-menu-open');
    }

    $(document).ready(function() {
        // Toggle submenu open/close
        $('.menu-toggle').on('click', function(e) {
            e.preventDefault();
            var $parent = $(this).parent('.menu-item');
            if ($parent.hasClass('open')) {
                $parent.removeClass('open');
            } else {
                $('.menu-item.open').removeClass('open'); // Close other open menus
                $parent.addClass('open');
            }
        });
        
        // Keep menu open if current page is in sub-menu
        var currentUrl = window.location.pathname.split('/').pop();
        $('.menu-sub .menu-link').each(function() {
            var href = $(this).attr('href');
            if (href === currentUrl) {
                $(this).addClass('active');
                $(this).closest('.menu-item').addClass('active');
                $(this).closest('.menu-sub').parent('.menu-item').addClass('open active');
            }
        });

        // Highlight active menu item
        $('.menu-inner .menu-link').each(function() {
            var href = $(this).attr('href');
            if (href === currentUrl) {
                $(this).closest('.menu-item').addClass('active');
            }
        });

        $('.menu-inner a.menu-link:not(.menu-toggle)').on('click', function() {
            if (window.innerWidth < 1200) {
                closeAdminMenu();
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAdminMenu();
            }
        });

        $(window).on('resize', function() {
            if (window.innerWidth >= 1200) {
                closeAdminMenu();
            }
        });

        // Auto-activate tab based on URL hash
        var hash = window.location.hash;
        if (hash) {
            var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
            if (triggerEl) {
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }
    });
    
document.addEventListener('DOMContentLoaded', function () {
  let menuToggle = document.querySelector('.layout-menu-toggle');
  if (menuToggle) {
    menuToggle.onclick = function () {
      document.documentElement.classList.toggle('layout-menu-expanded');
    };
  }
});
</script>
   
</body>
</html>
