/**
 * Welcome to your Workbox-powered service worker!
 *
 * You'll need to register this file in your web app and you should
 * disable HTTP caching for this file too.
 * See https://goo.gl/nhQhGp
 *
 * The rest of the code is auto-generated. Please don't update this file
 * directly; instead, make changes to your Workbox build configuration
 * and re-run your build process.
 * See https://goo.gl/2aRDsh
 */

importScripts("https://storage.googleapis.com/workbox-cdn/releases/3.5.0/workbox-sw.js");

/**
 * The workboxSW.precacheAndRoute() method efficiently caches and responds to
 * requests for URLs in the manifest.
 * See https://goo.gl/S9QRab
 */
self.__precacheManifest = [
  {
    "url": "404.html",
    "revision": "ceed3524a7f6b9a13b0a82e8174ee543"
  },
  {
    "url": "assets/css/0.styles.99d64aad.css",
    "revision": "6d879da2e6e9fff94f5996768f035281"
  },
  {
    "url": "assets/img/search.83621669.svg",
    "revision": "83621669651b9a3d4bf64d1a670ad856"
  },
  {
    "url": "assets/js/2.285762ea.js",
    "revision": "93afbd4e91ed17280e84a6930dc81b17"
  },
  {
    "url": "assets/js/3.7c0b9fb1.js",
    "revision": "62743958309dab2c52ea9a790514ef3b"
  },
  {
    "url": "assets/js/4.469a6cfd.js",
    "revision": "be6989d9a2453cf05b25af11136c88f8"
  },
  {
    "url": "assets/js/5.63646575.js",
    "revision": "c840bbbf221bcf80f3b1ee654bd64e71"
  },
  {
    "url": "assets/js/6.f85a9cd6.js",
    "revision": "aa6fdd609a2549f3c748e70bd13b4912"
  },
  {
    "url": "assets/js/7.f7c627df.js",
    "revision": "e6d8738db20d0cab90ff2e7cf1f90e1f"
  },
  {
    "url": "assets/js/8.d4894def.js",
    "revision": "dc5edc505a601927d3b643e3b10727e1"
  },
  {
    "url": "assets/js/9.b2d5e87f.js",
    "revision": "2b7ac4a0ba3ed221cbe6d8c6a4e6465d"
  },
  {
    "url": "assets/js/app.0c398e2d.js",
    "revision": "57c277b8905dc155a79d4d8a0de51a47"
  },
  {
    "url": "changelog.html",
    "revision": "c879604265c03f678f8155e23f34fb57"
  },
  {
    "url": "guide/api-reference.html",
    "revision": "04118710f226f920cf380898698318b7"
  },
  {
    "url": "guide/index.html",
    "revision": "ec204be7023200ec5da22dbe0a5f06f0"
  },
  {
    "url": "guide/installing.html",
    "revision": "7e03db87ffd1c67bb605d770ac8c8ca8"
  },
  {
    "url": "guide/using.html",
    "revision": "81e998952fa57c3f1135ec30d1b860d2"
  },
  {
    "url": "index.html",
    "revision": "ca61194ea4d49b93de6f9979bcb15839"
  }
].concat(self.__precacheManifest || []);
workbox.precaching.suppressWarnings();
workbox.precaching.precacheAndRoute(self.__precacheManifest, {});
addEventListener('message', event => {
  const replyPort = event.ports[0]
  const message = event.data
  if (replyPort && message && message.type === 'skip-waiting') {
    event.waitUntil(
      self.skipWaiting().then(
        () => replyPort.postMessage({ error: null }),
        error => replyPort.postMessage({ error })
      )
    )
  }
})
