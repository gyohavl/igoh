// send events to google analytics
document.querySelectorAll('a').forEach(el => {
    el.addEventListener('click', (event) => {
        gtag('event', 'click', {
            'event_label': el,
            'transport_type': 'beacon'
        });
    })
});

// getclass for 8.B
fetch('prumer/?getclass=1').then(r => r.text()).then(t => {
    if (t == '8.B') {
        document.querySelectorAll('.only8b').forEach(el => el.removeAttribute('style'));
    }
    let classes = { '': 'ZL', '???': 'ZL', '1.A': 'ZO', '1.B': 'ZP', '1.C': 'ZQ', '2.A': 'ZL', '2.B': 'ZM', '2.C': 'ZN', '3.A': 'ZI', '3.B': 'ZJ', '3.C': 'ZK', '4.A': 'ZE', '4.B': 'ZF', '4.C': 'ZG', '5.A': 'ZB', '5.B': 'ZC', '6.A': 'Z8', '6.B': 'Z9', '7.A': 'Z5', '7.B': 'Z6', '8.A': 'Z2', '8.B': 'Z3' };
    document.getElementById('ttpublic').setAttribute('href', `/b/timetable/public/Actual/class/${classes[t]}%3FTouchMode=1`);
});

// console log for devs
console.log("  _  _____  ____  _    _ \n (_)/ ____|/ __ \\| |  | |\n  _| |  __| |  | | |__| |\n | | | |_ | |  | |  __  |\n | | |__| | |__| | |  | |\n |_|\\_____|\\____/|_|  |_|\n");
// console.log("Hledám někoho, kdo by tento web převzal.\nJestli to chceš být ty, napiš mi na vit.kolos@gmail.com. Díky!");

// getting rid of old sw.js
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function (registrations) {
        for (let registration of registrations) {
            registration.unregister()
        }
    }).catch(function (err) {
        console.log('Service Worker registration failed: ', err);
    });
}

// handle 404
if (location.search.includes('?404=')) {
    document.getElementsByTagName('h1')[0].textContent += ' (stránka nenalezena)';
}
