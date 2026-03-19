document.addEventListener('DOMContentLoaded', () => {
    // Detect the user's timezone
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    // Send the timezone to WordPress via AJAX
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'd2g_save_user_timezone',
            timezone: timezone,
        }),
    })
    .then(response => response.json())
    .then(data => {
        //console.log('Timezone saved:', data);
    })
    .catch(error => {
        console.error('Error saving timezone:', error);
    });
});