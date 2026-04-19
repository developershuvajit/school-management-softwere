importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyDJzwXLEYtQbp79qiD6kd8tLDkprS6R2h0",
  authDomain: "schoolindiajunior.firebaseapp.com",
  projectId: "schoolindiajunior",
  storageBucket: "schoolindiajunior.firebasestorage.app",
  messagingSenderId: "607392322546",
  appId: "1:607392322546:web:d3b5b5575887253b44116b",
  measurementId: "G-7DCYY4Y5RN"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/favicon.ico'
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
