</div>
    
    <script>
        // Konfirmasi hapus
        function confirmDelete(url, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus ' + nama + '?')) {
                window.location.href = url;
            }
        }
        
        // Auto hide alert
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
    </script>
</body>
</html>