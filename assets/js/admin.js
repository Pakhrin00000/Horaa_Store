/**
 * HORAA STORE - Admin Dashboard Interactivity
 */

document.addEventListener('DOMContentLoaded', function () {
    // Delete confirmation prompt
    document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Dynamic Specification Pair Builder
    const addSpecBtn = document.getElementById('btn-add-spec');
    const specContainer = document.getElementById('spec-pairs-container');

    if (addSpecBtn && specContainer) {
        addSpecBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 spec-row';
            row.innerHTML = `
                <div class="col-5">
                    <input type="text" name="spec_keys[]" class="form-control form-control-cyber" placeholder="Key (e.g. Material)">
                </div>
                <div class="col-6">
                    <input type="text" name="spec_values[]" class="form-control form-control-cyber" placeholder="Value (e.g. Aluminum)">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-remove-spec"><i class="fas fa-trash"></i></button>
                </div>
            `;
            specContainer.appendChild(row);
        });

        specContainer.addEventListener('click', function (e) {
            if (e.target.closest('.btn-remove-spec')) {
                e.target.closest('.spec-row').remove();
            }
        });
    }
});
