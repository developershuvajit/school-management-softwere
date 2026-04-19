// Import Firebase modules
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

// Your Firebase config (same as yours)
const firebaseConfig = {
  apiKey: "AIzaSyDJzwXLEYtQbp79qiD6kd8tLDkprS6R2h0",
  authDomain: "schoolindiajunior.firebaseapp.com",
  projectId: "schoolindiajunior",
  storageBucket: "schoolindiajunior.firebasestorage.app",
  messagingSenderId: "607392322546",
  appId: "1:607392322546:web:d3b5b5575887253b44116b",
  measurementId: "G-7DCYY4Y5RN"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Initialize Firebase Messaging
const messaging = getMessaging(app);

export function requestNotificationPermissionAndGetToken(userId) {
  return Notification.requestPermission()
    .then(permission => {
      if (permission === 'granted') {
        console.log('Notification permission granted.');

        return getToken(messaging, { vapidKey: 'YOUR_PUBLIC_VAPID_KEY_HERE' });
      } else {
        throw new Error('Notification permission not granted');
      }
    })
    .then(token => {
      console.log('FCM Token:', token);

      // Send this token to your backend to save it linked with userId (parent)
      fetch('/api/save_token.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ user_id: userId, token: token })
      });

      return token;
    })
    .catch(console.error);
}

// Listen for messages when app is in foreground
onMessage(messaging, (payload) => {
  console.log('Message received. ', payload);
  alert(`${payload.notification.title}\n${payload.notification.body}`);
});
