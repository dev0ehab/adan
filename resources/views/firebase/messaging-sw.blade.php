importScripts('https://www.gstatic.com/firebasejs/11.0.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.0.2/firebase-messaging-compat.js');
firebase.initializeApp(@json($firebaseConfig, JSON_UNESCAPED_SLASHES));
const messaging = firebase.messaging();
messaging.onBackgroundMessage(function (payload) {
  const title = payload.notification && payload.notification.title ? payload.notification.title : '';
  const body = payload.notification && payload.notification.body ? payload.notification.body : '';
  return self.registration.showNotification(title, { body: body, data: payload.data || {} });
});
