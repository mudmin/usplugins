<?php
$categories = $db->query("SELECT * FROM plg_faq_categories ORDER BY display_order")->results();
$faqs = [];
foreach ($categories as $category) {
    $faqs[$category->id] = $db->query("SELECT * FROM plg_faqs WHERE category_id = ? ORDER BY display_order", [$category->id])->results();
}

//you can override load your view of this instead of ours if you would like.
if (file_exists($abs_us_root . $us_url_root . 'usersc/plugins/faq/faq_custom.php')) {
    require_once $abs_us_root . $us_url_root . 'usersc/plugins/faq/faq_custom.php';
} else {
?>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Section -->
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-normal text-dark mb-3">Frequently Asked Questions</h1>
                    <p class="lead text-muted">Find answers to common questions quickly and easily</p>
                </div>

                <!-- Search Section -->
                <div class="row justify-content-center mb-4">
                    <div class="col-10 offset-1 col-lg-6 offset-lg-3">
                        <div class="input-group input-group-lg px-3">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="faq-search" class="form-control border-start-0 ps-0"
                                placeholder="Search FAQs..." autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Categories Navigation -->
                    <div class="col-lg-3 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="card-title mb-0 fw-semibold text-dark">Categories</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($categories as $category) { ?>
                                    <a href="#category-<?= $category->id ?>"
                                        class="list-group-item list-group-item-action border-0 py-3 category-link">
                                        <i class="fas fa-folder-open me-2 text-muted"></i>
                                        <?= hed($category->menu_text) ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Content -->
                    <div class="col-lg-9">
                        <?php foreach ($categories as $category) { ?>
                            <div id="category-<?= $category->id ?>" class="faq-category mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-primary rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-question text-white d-flex align-items-center justify-content-center h-100"></i>
                                    </div>
                                    <h2 class="h3 mb-0 text-dark fw-normal"><?= hed($category->name) ?></h2>
                                </div>

                                <div class="accordion" id="accordion-<?= $category->id ?>">
                                    <?php foreach ($faqs[$category->id] as $index => $faq) { ?>
                                        <div class="accordion-item border-0 mb-3 shadow-sm faq-item">
                                            <h4 class="accordion-header">
                                                <button class="accordion-button collapsed bg-light fw-normal py-3"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-<?= $category->id ?>-<?= $index ?>"
                                                    aria-expanded="false"
                                                    aria-controls="collapse-<?= $category->id ?>-<?= $index ?>">
                                                    <?= hed($faq->question) ?>
                                                </button>
                                            </h4>
                                            <div id="collapse-<?= $category->id ?>-<?= $index ?>"
                                                class="accordion-collapse collapse"
                                                data-bs-parent="#accordion-<?= $category->id ?>">
                                                <div class="accordion-body bg-white py-3">
                                                    <div class="accordion-body bg-white py-0">
                                                        <?= hed($faq->answer) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- No Results Message -->
                        <div id="no-results" class="text-center py-5" style="display: none;">
                            <div class="text-muted">
                                <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                                <h5 class="text-muted">No FAQs found</h5>
                                <p>Try adjusting your search terms</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (file_exists($abs_us_root . $us_url_root . 'usersc/plugins/faq/faq_style_custom.php')) {
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/faq/faq_style_custom.php';
    } else {
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/faq/faq_style.php';
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('faq-search');
            const faqItems = document.querySelectorAll('.faq-item');
            const faqCategories = document.querySelectorAll('.faq-category');
            const categoryLinks = document.querySelectorAll('.category-link');
            const noResults = document.getElementById('no-results');

            // Search functionality
            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleItemsCount = 0;

                // Clear previous highlights
                document.querySelectorAll('.highlight').forEach(el => {
                    el.outerHTML = el.innerHTML;
                });

                faqItems.forEach(function(item) {
                    const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                    const answer = item.querySelector('.accordion-body').textContent.toLowerCase();

                    if (searchTerm === '' || question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                        visibleItemsCount++;

                        // Highlight search terms
                        if (searchTerm !== '') {
                            // do nothing as this function doesn't strip HTML correctly
                            // highlightSearchTerm(item, searchTerm);
                        }
                    } else {
                        item.style.display = 'none';
                        // Close accordion if it's open
                        const collapse = item.querySelector('.accordion-collapse');
                        if (collapse.classList.contains('show')) {
                            bootstrap.Collapse.getOrCreateInstance(collapse).hide();
                        }
                    }
                });

                // Show/hide categories based on visible items
                faqCategories.forEach(function(category) {
                    const visibleItems = category.querySelectorAll('.faq-item[style*="display: block"], .faq-item:not([style*="display: none"])');
                    if (visibleItems.length > 0 && (searchTerm === '' || Array.from(visibleItems).some(item => item.style.display !== 'none'))) {
                        category.style.display = 'block';
                    } else {
                        category.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (visibleItemsCount === 0 && searchTerm !== '') {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }

                // If searching, expand first matching accordion
                if (searchTerm !== '' && visibleItemsCount > 0) {
                    const firstVisible = document.querySelector('.faq-item[style*="display: block"] .accordion-collapse, .faq-item:not([style*="display: none"]) .accordion-collapse');
                    if (firstVisible && !firstVisible.classList.contains('show')) {
                        bootstrap.Collapse.getOrCreateInstance(firstVisible).show();
                    }
                }
            });

            // Highlight search terms function
            function highlightSearchTerm(item, searchTerm) {
                const button = item.querySelector('.accordion-button');
                const body = item.querySelector('.accordion-body');

                [button, body].forEach(element => {
                    if (element) {
                        const text = element.innerHTML;
                        const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                        element.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
                    }
                });
            }

            // Escape special regex characters
            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Category navigation
            categoryLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    categoryLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');

                    // Smooth scroll to category
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Update active category link on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        categoryLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${id}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            }, {
                rootMargin: '-50% 0px -50% 0px'
            });

            faqCategories.forEach(category => {
                observer.observe(category);
            });
        });
    </script>
<?php } ?>
