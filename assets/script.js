// send events to google analytics
// document.querySelectorAll('a').forEach(el => {
//     el.addEventListener('click', (event) => {
//         gtag('event', 'click', {
//             'event_label': el,
//             'transport_type': 'beacon'
//         });
//     })
// });

// getclass for timetable link
fetch('prumer/?getclass=2').then(r => r.text()).then(t => {
    t = t || 'ZR';
    document.getElementById('ttpublic').setAttribute('href', `/b/timetable/public/Actual/class/${t}%3FTouchMode=1`);
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
