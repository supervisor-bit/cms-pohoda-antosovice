        </main>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Fix gray text on gradient backgrounds -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find all small.text-muted elements in tables
        const greyTexts = document.querySelectorAll('.table small.text-muted, table small.text-muted, br + small.text-muted');
        greyTexts.forEach(function(element) {
            element.style.color = '#ffffff';
            element.style.fontWeight = '600';
            element.style.textShadow = '0 1px 3px rgba(0,0,0,0.7)';
            element.style.background = 'rgba(0,0,0,0.15)';
            element.style.padding = '3px 6px';
            element.style.borderRadius = '4px';
        });
    });
    </script>
</body>
</html>
