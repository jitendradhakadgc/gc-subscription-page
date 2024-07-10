<?php
/*
Template Name: Customer Need
*/

get_header(); 

// Check if the form has been submitted
if (isset($_POST['customer_need_form']) && $_POST['customer_need_form'] == '1') {
    // Check nonce for security
    if (!isset($_POST['customer_need_form_nonce_field']) || !wp_verify_nonce($_POST['customer_need_form_nonce_field'], 'customer_need_form_nonce')) {
        echo '<p>Nonce verification failed</p>';
    } else {
        // Validate and sanitize the form data
        $wifi = sanitize_text_field($_POST['wifi']);
        $pet_detail = sanitize_text_field($_POST['pet_detail']);
        $flexible_days = sanitize_text_field($_POST['flexible_days']);
        $radius = sanitize_text_field($_POST['radius']);

        echo '<p>Thank you for submitting your customer need details!</p>';
        echo '<p>Wifi: ' . esc_html($wifi) . '</p>';
        echo '<p>Pet Detail: ' . esc_html($pet_detail) . '</p>';
        echo '<p>Flexible Days: ' . esc_html($flexible_days) . '</p>';
        echo '<p>Radius: ' . esc_html($radius) . '</p>';
    }
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <h1>Customer Need</h1>
        <p>Please fill out the form below to submit your customer needs:</p>

        <form action="<?php echo esc_url(get_permalink()); ?>" method="POST">
            <input type="hidden" name="customer_need_form" value="1">
            <?php wp_nonce_field('customer_need_form_nonce', 'customer_need_form_nonce_field'); ?>

            <p>
                <label for="wifi">Wifi</label><br>
                <input type="text" id="wifi" name="wifi" required>
            </p>
            <p>
                <label for="pet_detail">Pet Detail</label><br>
                <input type="text" id="pet_detail" name="pet_detail" required>
            </p>
            <p>
                <label for="flexible_days">Flexible Days</label><br>
                <input type="text" id="flexible_days" name="flexible_days" required>
            </p>
            <p>
                <label for="flexible_days">Flexible Days</label><br>
                <input type="radio" id="flexible_yes" name="flexible_days" value="Yes" required> <label for="flexible_yes">Yes</label>
                <input type="radio" id="flexible_no" name="flexible_days" value="No"> <label for="flexible_no">No</label>
            </p>
            <p>
                <label for="radius">Radius</label><br>
                <input type="text" id="radius" name="radius" required>
            </p>
            <p>
                <input type="submit" value="Submit">
            </p>
        </form>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>