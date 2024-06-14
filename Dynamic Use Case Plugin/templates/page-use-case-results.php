<?php
/* Template Name: Use Case Results */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();

$query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

?>

<div class="use-case-results-header">
    <div class="use-case-results-logo">
        <a href="<?php echo home_url(); ?>">
            <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/your-logo.png'; ?>" alt="Logo">
        </a>
    </div>
    <form class="use-case-results-search-form" action="<?php echo home_url('/use-case-results/'); ?>" method="get">
        <input type="text" name="q" value="<?php echo esc_attr($query); ?>" placeholder="Search Use Cases...">
        <button type="submit">Search</button>
    </form>
</div>

<div class="use-case-results-container">
    <h1>Search Results for "<?php echo esc_html($query); ?>"</h1>
    <div id="use-case-results"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var query = '<?php echo esc_js($query); ?>';
        
        fetch(`<?php echo home_url('/wp-json/my-custom-plugin/v1/search-use-cases?q='); ?>${query}`, {
            headers: {
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            var resultsContainer = document.getElementById('use-case-results');
            resultsContainer.innerHTML = '';
            if (data.length) {
                data.forEach(result => {
                    var resultItem = document.createElement('div');
                    resultItem.className = 'result-item';
                    resultItem.innerHTML = `
                        <div class="result-header">
                            <a href="${result.url}" class="result-title">${result.title}</a>
                            <a href="${result.url}" class="result-url">${result.url}</a>
                        </div>
                        <p class="result-description">${result.description}</p>
                    `;
                    resultsContainer.appendChild(resultItem);
                });
            } else {
                resultsContainer.innerHTML = '<p>No results found</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching search results:', error);
        });
    });
</script>

<style>
    .use-case-results-header {
        display: flex;
        align-items: center;
        padding: 20px;
        background-color: #202124;
    }
    .use-case-results-logo img {
        height: 40px;
        margin-right: 20px;
    }
    .use-case-results-search-form {
        display: flex;
        flex-grow: 1;
    }
    .use-case-results-search-form input[type="text"] {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px 0 0 4px;
        outline: none;
    }
    .use-case-results-search-form button {
        padding: 10px 20px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 0 4px 4px 0;
        background-color: #5f6368;
        color: #fff;
        cursor: pointer;
    }
    .use-case-results-container {
        padding: 20px;
    }
    .result-item {
        border-bottom: 1px solid #ccc;
        padding: 10px 0;
    }
    .result-header {
        display: flex;
        flex-direction: column;
    }
    .result-title {
        font-size: 1.2em;
        color: #1a0dab;
        text-decoration: none;
    }
    .result-title:hover {
        text-decoration: underline;
    }
    .result-url {
        font-size: 0.9em;
        color: #006621;
        text-decoration: none;
    }
    .result-description {
        margin: 5px 0 0;
        font-size: 1em;
        color: #545454;
    }
</style>

<?php
get_footer();
