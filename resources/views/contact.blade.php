@extends('layouts.attendance')

@section('title', 'Contact Us')

@section('navigation')
    <li><a href="{{ route('home') }}">Home</a></li>
    <li><a href="{{ route('contact') }}" class="active">Message</a></li>
    <li><a href="{{ route('employee.info') }}">Emp_Info</a></li>
    <li><a href="{{ route('admin.login') }}">Admin</a></li>
@endsection

@push('styles')
<style>
    .contact-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 0;
    }

    .contact-card {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .contact-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .contact-header h2 {
        color: #333;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .contact-header p {
        color: #666;
        font-size: 1.2rem;
        line-height: 1.6;
    }

    .contact-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .info-item {
        text-align: center;
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        background: white;
    }

    .info-item i {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .info-item h4 {
        color: #333;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .info-item p {
        color: #666;
        margin: 0;
        font-size: 1.1rem;
    }

    .contact-form {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 15px;
        margin-top: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .developer-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        margin-top: 3rem;
    }

    .developer-info h3 {
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .developer-info p {
        margin: 0.5rem 0;
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<div class="contact-container">
    <div class="contact-card">
        <div class="contact-header">
            <h2>Get In Touch</h2>
            <p>Have questions about the QR Attendance System? We're here to help!</p>
        </div>

        <div class="contact-info">
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <h4>Email</h4>
                <p>tcheumanisinakjusto@gmail.com</p>
            </div>
            
            <div class="info-item">
                <i class="fas fa-code"></i>
                <h4>Developer</h4>
                <p>TcheumaniSinakJusto</p>
            </div>
            
            <div class="info-item">
                <i class="fas fa-calendar"></i>
                <h4>Year</h4>
                <p>{{ date('Y') }}</p>
            </div>
        </div>

        <div class="contact-form">
            <h3 class="mb-4 text-center">
                <i class="fas fa-paper-plane me-2"></i>
                Send us a Message
            </h3>
            
            <form id="contactForm">
                @csrf
                
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="fas fa-user me-1"></i>
                        Your Name
                    </label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label">
                        <i class="fas fa-tag me-1"></i>
                        Subject
                    </label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">
                        <i class="fas fa-comment me-1"></i>
                        Message
                    </label>
                    <textarea id="message" name="message" class="form-control" 
                              placeholder="Tell us about your inquiry..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane me-2"></i>
                    Send Message
                </button>
            </form>
        </div>

        <div class="developer-info">
            <h3>About the Developer</h3>
            <p><strong>TcheumaniSinakJusto</strong></p>
            <p>Full Stack Developer specializing in web applications</p>
            <p>Expert in PHP, Laravel, JavaScript, and modern web technologies</p>
            <p class="mt-3">
                <i class="fas fa-copyright me-1"></i>
                Copyright {{ date('Y') }} - All Rights Reserved
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $('.submit-btn');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');
        submitBtn.prop('disabled', true);
        
        // Simulate form submission (since we don't have a backend endpoint)
        setTimeout(function() {
            // Show success message
            alert('Thank you for your message! We will get back to you soon.');
            
            // Reset form
            $('#contactForm')[0].reset();
            
            // Restore button
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 2000);
    });
});
</script>
@endpush
@endsection
