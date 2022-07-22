(function (navigator, window) {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/service-worker-script', {
        scope: '/'
      }).then(registration => {
        console.log(registration.scope);
      }).catch(err => {
        console.log(err);
      });
    })
  }
})(navigator, window)
