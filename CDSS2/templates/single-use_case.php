<?php
get_header();

// Enqueue the CSS file for the template
wp_enqueue_style('use-case-template', plugin_dir_url(__FILE__) . '../assets/css/use-case-template.css');

// Debugging: Check if the correct template is being loaded
echo '<!-- single-use_case.php template loaded -->';

// Fetch post meta data
$use_case_thumbnail_url = get_post_meta(get_the_ID(), 'use_case_thumbnail_url', true);
$use_case_description = get_post_meta(get_the_ID(), 'use_case_description', true);
$use_case_solution_text = get_post_meta(get_the_ID(), 'use_case_solution_text', true);
$use_case_result_text = get_post_meta(get_the_ID(), 'use_case_result_text', true);
$use_case_cta_text = get_post_meta(get_the_ID(), 'use_case_cta_text', true);
$use_case_cta_url = get_post_meta(get_the_ID(), 'use_case_cta_url', true);

// Debugging: Print meta values
echo '<!-- ';
var_dump($use_case_thumbnail_url);
var_dump($use_case_description);
var_dump($use_case_solution_text);
var_dump($use_case_result_text);
var_dump($use_case_cta_text);
var_dump($use_case_cta_url);
echo ' -->';
?>
<style>
    .UseContainer {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
    }

    .UseContent {
        width: 70%;
        padding-top: 60px;
        padding-bottom: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 40px;
    }

    .imgthum {
        width: 80%;
        height: auto;
    }

    .ThumbnailImage {
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    .UseTitleDis {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        gap: 44px;
    }

    .UseTitle {
        text-align: center;
        color: #275B42;
        font-size: 36px;
        font-family: Montserrat, sans-serif;
        font-weight: 700;
        word-wrap: break-word;
        line-height: 46px;
    }

    .UseDiscription {
        text-align: center;
        color: black;
        font-size: 24px;
        font-family: Montserrat, sans-serif;
        font-weight: 300;
        word-wrap: break-word;
    }

    .UseSolution,
    .UseResult {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 14px;
    }

    .SolutionHead,
    .Result {
        text-align: center;
        color: #275B42;
        font-size: 24px;
        font-family: Montserrat, sans-serif;
        font-weight: 600;
        word-wrap: break-word;
    }

    .UseSolutionText,
    .UseResultText {
        width: 100%;
        max-width: 852px;
        color: #444B5B;
        font-size: 18px;
        font-family: Roboto, sans-serif;
        font-weight: 400;
        line-height: 28px;
        word-wrap: break-word;
    }

    .UseCta {
        width: 100%;
        background: #1C1C1C;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 43px;
        padding: 20px 0;
        min-height: 200px;
        font-size: 16px;
    }

    .OptimizingBrokerPerformance {
        text-align: center;
        color: white;
        font-size: 32px;
        font-family: Montserrat, sans-serif;
        font-weight: 600;
        word-wrap: break-word;
    }

    .CtaButton {
        width: auto;
    }

    .CtaText {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #275B42;
        font-family: Montserrat, sans-serif;
        font-weight: 700;
        text-decoration: none;
        position: relative;
        z-index: 1;
        background-color: #ffffff;
        border-radius: 8px;
        padding: 10px 45px 10px 45px;
    }

    .CtaText:hover {
        color: #ffffff;
        background-color: #275B42;
    }


    @media (max-width: 768px) {
        .UseContent {
            padding-top: 40px;
            padding-bottom: 40px;
            width: 90%;
        }

        .UseTitle {
            font-size: 36px;
            line-height: 46px;
        }

        .UseDiscription p {
            font-size: 32px !important;
            line-height: 40px;
        }

        .SolutionHead,
        .Result {
            font-size: 28px;
            line-height: 38px;
        }

        .UseSolutionText,
        .UseResultText {
            font-size: 18px;
            line-height: 28px;
        }

        .OptimizingBrokerPerformance {
            font-size: 28px;
        }

        .CtaButton {
            width: auto;
        }

        .Rectangle12,
        .CtaText {
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        .UseContent {
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .UseTitle {
            font-size: 32px;
            line-height: 42px;
        }

        .UseDiscription p {
            font-size: 28px !important;
            line-height: 40px;
        }

        .SolutionHead,
        .Result {
            font-size: 24px;
            line-height: 34px;
        }

        .UseSolutionText,
        .UseResultText {
            font-size: 16px;
            line-height: 26px;
        }

        .OptimizingBrokerPerformance {
            font-size: 24px;
        }

        .CtaButton {
            width: auto;
        }

        .Rectangle12,
        .CtaText {
            font-size: 12px;
        }
    }
</style>
<div class="UseContainer">
    <div class="UseContent">
        <div class="imgthum"><img class="ThumbnailImage" src="<?php echo esc_url($use_case_thumbnail_url); ?>" alt="<?php the_title(); ?>" width="650" height="350" /></div>
        <div class="UseTitleDis">
            <div class="UseTitle"><?php the_title(); ?></div>
            <div class="UseDiscription"><?php the_content(); ?></div>
        </div>
        <div class="UseSolution">
            <div class="SolutionHead">Solution</div>
            <div class="UseSolutionText"><?php echo esc_html($use_case_solution_text); ?></div>
        </div>
        <div class="UseResult">
            <div class="Result">Result</div>
            <div class="UseResultText"><?php echo esc_html($use_case_result_text); ?></div>
        </div>
    </div>
    <div class="UseCta">
        <div class="OptimizingBrokerPerformance">Optimizing Broker Performance</div>
        <div class="CtaButton">

            <a href="https://thelenders.app/request-a-demo/" class="CtaText"><?php echo esc_html($use_case_cta_text); ?></a>
        </div>
    </div>
</div>

<?php get_footer(); ?>