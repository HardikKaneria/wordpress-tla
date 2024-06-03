<?php
/*
Template Name: Use Cases Page
*/

get_header();

?>

<div class="UseCasesContainer">
    <h1 class="UseCasesTitle">Use Cases</h1>
    <div class="UseCasesList">
        <?php
        // Query for use case posts
        $args = array(
            'post_type' => 'use_case',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $use_cases_query = new WP_Query($args);

        if ($use_cases_query->have_posts()) :
            while ($use_cases_query->have_posts()) : $use_cases_query->the_post();
        ?>
                <div class="UseCaseCard">
                    <?php if (has_post_thumbnail()) : ?>
                        <img class="UseCaseThumbnail" src="<?php the_post_thumbnail_url('full'); ?>" alt="<?php the_title(); ?>">
                    <?php endif; ?>
                    <h2 class="UseCaseTitle"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="UseCaseExcerpt"><?php echo get_the_excerpt(); ?></p>
                    <a class="UseCaseReadMore" href="<?php the_permalink(); ?>">Read More</a>
                </div>
        <?php
            endwhile;
        else :
            echo '<p>No use cases found.</p>';
        endif;
        wp_reset_postdata();
        ?>
    </div>
</div>

<?php get_footer(); ?>
