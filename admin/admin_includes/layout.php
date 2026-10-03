<?php
/* Admin pages use the shared application shell. Usage: $me = admin_header('Title', 'dashboard'); ... admin_footer(); */

function admin_header(string $title, string $active): array
{
    return page_header($title, $active, 'admin');
}

function admin_footer(): void
{
    page_footer();
}
