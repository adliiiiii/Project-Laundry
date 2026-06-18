    <div class="footer">&copy; <?= date('Y') ?> White Clean</div>
</div><!-- end .container -->

<script>
function bukaModal(id){ document.getElementById(id).classList.add('active'); }
function tutupModal(id){ document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(el=>{
    el.addEventListener('click',function(e){ if(e.target===this) this.classList.remove('active'); });
});
</script>
</body>
</html>
