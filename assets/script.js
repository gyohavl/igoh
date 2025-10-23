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
    t = t || 'ZZ';
    document.getElementById('ttpublic').setAttribute('href', `https://gyohavl.bakalari.cz/timetable/public/Actual/class/${t}?TouchMode=1`);
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


try {
    let url = new URL(location);

    if (url.searchParams.get('from') == 'tk'
        && document.querySelector('h1').textContent == 'iGOH') {
        document.querySelector('h1').textContent = 'iGOH.cz';
    }

    if (url.searchParams.has('404')) {
        document.querySelector('h1').textContent += ' (stránka nenalezena)';
    }

    let original = url.href;
    url.searchParams.delete('fbclid');
    url.searchParams.delete('odhlasit');
    url.searchParams.delete('from');
    let replace = url.href;

    if (original != replace) {
        // let analytics log the params
        setTimeout(function () { history.replaceState(null, '', replace); }, 1000);
    }
} catch (error) { }
