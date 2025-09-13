// Register validation
function validateRegister(e){
  const name = document.getElementById("regName");
  const email = document.getElementById("regEmail");
  const pass = document.getElementById("regPassword");
  const role = document.getElementById("regRole");
  if (!name || !email || !pass || !role) return true; // nothing to validate
  document.getElementById("regNameError").innerText="";
  document.getElementById("regEmailError").innerText="";
  document.getElementById("regPassError").innerText="";
  document.getElementById("regRoleError").innerText="";
  let ok=true;
  if (name.value.trim().length < 3) { document.getElementById("regNameError").innerText="Name at least 3 chars"; ok=false; }
  const ep = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  if (!ep.test(email.value.trim())) { document.getElementById("regEmailError").innerText="Enter valid email"; ok=false; }
  if (pass.value.trim().length < 6) { document.getElementById("regPassError").innerText="Min 6 chars"; ok=false; }
  if (role.value === "") { document.getElementById("regRoleError").innerText="Select role"; ok=false; }
  return ok;
}

// Login validation
function validateLogin(e){
  const email = document.getElementById("email"), pass = document.getElementById("password");
  document.getElementById("loginEmailError").innerText=""; document.getElementById("loginPassError").innerText="";
  let ok=true;
  if (!email || email.value.trim() === "") { document.getElementById("loginEmailError").innerText="Required"; ok=false; }
  if (!pass || pass.value.trim() === "") { document.getElementById("loginPassError").innerText="Required"; ok=false; }
  return ok;
}

// small helper (can extend)
function confirmAction(msg){ return confirm(msg || "Are you sure?"); }
function updatePreview(){
  const sel = document.getElementById('quickProduct');
  if(!sel) return;
  const imgName = sel.selectedOptions[0].dataset.img || 'placeholder.png';
  const preview = document.getElementById('quickPreview');
  if(preview) preview.src = "../images/products/" + imgName;
}
// call once on load
document.addEventListener('DOMContentLoaded', ()=>{ try{ updatePreview(); }catch(e){} });

// Image upload preview functionality
function initImageUploadPreview() {
  const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
  
  imageInputs.forEach(input => {
    input.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          // Find the nearest preview container or create one
          let previewContainer = input.closest('.form-row').querySelector('.image-preview');
          
          if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'image-preview';
            previewContainer.style.marginTop = '8px';
            input.closest('.form-row').appendChild(previewContainer);
          }
          
          previewContainer.innerHTML = `
            <img src="${e.target.result}" style="max-width: 150px; max-height: 150px; border-radius: 4px;">
            <div class="muted" style="font-size: 12px; margin-top: 4px;">Image preview</div>
          `;
        }
        
        reader.readAsDataURL(this.files[0]);
      }
    });
  });
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
  initImageUploadPreview();
  
  // Your existing code
  validateRegister(e);
  validateLogin(e);
  updatePreview();
});

