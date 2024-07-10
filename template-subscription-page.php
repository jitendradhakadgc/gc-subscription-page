<?php
/**
* Template Name: Subscription Page
*/

require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51NSgyuSF4Znl2mSMztM9hVKDfnoHzfnd5JIty14ciaEpuK3pVp665ZFac95E7jEqnnWLnXo9Nd610PMHslbYKRev00oAJLKfFV');

get_header(); 

// Check if the form has been submitted
if (isset($_POST['subscription_form']) && $_POST['subscription_form'] == '1') {
    // Check nonce for security
    if (!isset($_POST['subscription_form_nonce_field']) || !wp_verify_nonce($_POST['subscription_form_nonce_field'], 'subscription_form_nonce')) {
        echo '<p>Nonce verification failed</p>';
    } else {
        // Validate and sanitize the form data
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $company = sanitize_text_field($_POST['company']);
        $job_title = sanitize_text_field($_POST['job_title']);
        $payment_method_id = sanitize_text_field($_POST['payment_method_id']);

        // Check if email already exists
        if (email_exists($email)) {
            echo '<h1>Email already exists</h1>';
        } else {
            // Create a new user
            $user_id = wp_create_user($email, wp_generate_password(), $email);

            if (is_wp_error($user_id)) {
                echo '<p>Error creating user: ' . $user_id->get_error_message() . '</p>';
            } else {
                // Update user meta data
                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ));

                update_user_meta($user_id, 'phone', $phone);
                update_user_meta($user_id, 'company', $company);
                update_user_meta($user_id, 'job_title', $job_title);

                // Create Payment Intent
                try {
                    $paymentIntent = \Stripe\PaymentIntent::create([
                        'amount' => 5000, // Amount in cents (e.g., $50.00)
                        'currency' => 'usd',
                        'payment_method_types' => ['card'],
                        'payment_method' => $payment_method_id,
                        'confirm' => true, // Confirm the payment immediately
                        'description' => 'Subscription Payment',
                        'statement_descriptor' => 'Custom descriptor',
                    ]);

                    // Send a confirmation email to the user
                    $to = $email;
                    $subject = 'Subscription Confirmation';
                    $message = "Hello $first_name,\n\nThank you for subscribing!\n\nBest regards,\nYour Company";
                    $headers = array('Content-Type: text/plain; charset=UTF-8');
                    wp_mail($to, $subject, $message, $headers);

                    echo '<p>Thank you for subscribing! Check your email for a confirmation message.</p>';
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    echo '<p>Error processing payment: ' . $e->getMessage() . '</p>';
                }
            }
        }
    }
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <h1>Subscription Page</h1>
        <p>Please fill out the form below to subscribe:</p>

        <form id="subscription-form" action="<?php echo esc_url(get_permalink()); ?>" method="POST">
            <input type="hidden" name="subscription_form" value="1">
            <?php wp_nonce_field('subscription_form_nonce', 'subscription_form_nonce_field'); ?>

            <p>
                <label for="first_name">First Name</label><br>
                <input type="text" id="first_name" name="first_name" required>
            </p>
            <p>
                <label for="last_name">Last Name</label><br>
                <input type="text" id="last_name" name="last_name" required>
            </p>
            <p>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" required>
            </p>
            <p>
                <label for="phone">Phone Number</label><br>
                <input type="tel" id="phone" name="phone" required>
            </p>
            <p>
                <label for="company">Company</label><br>
                <input type="text" id="company" name="company">
            </p>
            <p>
                <label for="job_title">Job Title</label><br>
                <input type="text" id="job_title" name="job_title">
            </p>

            <!-- Stripe Payment Form -->
            <div id="payment-element"></div>
            <p>
                <button id="submit-button" class="stripe-payment-button">
                    <div class="spinner hidden" id="spinner"></div>
                    <span id="button-text">Subscribe</span>
                </button>
            </p>
            <div id="payment-message" class="hidden"></div>
        </form>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var stripe = Stripe('pk_test_51NSgyuSF4Znl2mSM6nOTxQ165gJhXKHiFap6Qpu40cOMFQuFNiTnYpDkd5Tao7Jls0QRHKyFW0m1gpBSBvk2Bxii00XpCypfPK');
        var elements = stripe.elements();
        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        var card = elements.create('card', { style: style });
        card.mount('#payment-element');

        var form = document.getElementById('subscription-form');
        var submitButton = document.getElementById('submit-button');

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            stripe.createPaymentMethod({
                type: 'card',
                card: card,
                billing_details: {
                    name: form.first_name.value + ' ' + form.last_name.value,
                    email: form.email.value,
                    phone: form.phone.value,
                    address: {
                        line1: "tes 1 ",
                        line2: "test 2",
                        city: "indore",
                        state: "MP",
                        postal_code: "452012",
                        country: "IN",
                    }
                }
            }).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('payment-message');
                    errorElement.textContent = result.error.message;
                    errorElement.classList.remove('hidden');
                    submitButton.disabled = false;
                    document.getElementById('button-text').style.display = 'block';
                    document.getElementById('spinner').style.display = 'none';
                } else {
                    // Set the payment_method_id field value
                    var paymentMethodIdField = document.createElement('input');
                    paymentMethodIdField.setAttribute('type', 'hidden');
                    paymentMethodIdField.setAttribute('name', 'payment_method_id');
                    paymentMethodIdField.setAttribute('value', result.paymentMethod.id);
                    form.appendChild(paymentMethodIdField);
                    // Submit the form
                    console.log('Form submitting...'); // Log to check form submission
                    form.submit(); // Submit the form
                }
            });
            // Disable the submit button to prevent multiple submissions
            submitButton.disabled = true;
            document.getElementById('button-text').style.display = 'none';
            document.getElementById('spinner').style.display = 'block';
        });
    });
</script>
