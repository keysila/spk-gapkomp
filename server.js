const phpServer = require('node-php-server');
const os = require('os');

const ip = Object.values(os.networkInterfaces())
  .flat()
  .find(i => i.family === 'IPv4' && !i.internal).address;

phpServer.createServer({
  port: 8080,
  hostname: ip,
  base: '.',          // folder aktif sekarang = spk-gapkomp
  keepalive: false,
  open: false,
  bin: 'php',
  router: 'index.php'
});

console.log(`Server berjalan di: http://${ip}:8080`);
