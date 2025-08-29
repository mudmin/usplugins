 <style>
        /* Custom CSS for enhanced styling */
        .category-link {
            transition: all 0.2s ease;
        }

        .category-link:hover {
            background-color: #f8f9fa;
            border-left: 3px solid #0d6efd;
            padding-left: 1rem;
        }

        .category-link.active {
            color: #212529;
			font-weight: bold;
			background-color: #e3f2fd;
            border-left: 3px solid #0d6efd;
            padding-left: 1rem;
        }

        .accordion-button:not(.collapsed) {
            background-color: #0d6efd;
            color: #ea0b09;
        }

        .accordion-button:not(.collapsed):hover {
            color: #ea0b09;
        }

        .accordion-button {
            color: #212529;
        }

        .accordion-button:hover {
            color: #0d6efd;
        }

        .accordion-button:focus {
            box-shadow: none;
            border: none;
        }

        .accordion-item {
            border-radius: 0.5rem !important;
            overflow: hidden;
        }

        .accordion-button {
            border: none;
            border-radius: 0.5rem;
        }

        .accordion-button:not(.collapsed) {
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
        }

        .form-control {
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .card {
            border-radius: 0.75rem;
        }

        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }

        /* Highlight matched search terms */
        .highlight {
            background-color: #fff3cd;
            padding: 0 2px;
            border-radius: 2px;
        }
    </style>