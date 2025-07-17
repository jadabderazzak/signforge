/**
 * SignForge - Document Form & Dynamic Item Rows
 * ---------------------------------------------
 * This script handles:
 * 1. Dynamic addition/removal of invoice item rows
 * 2. Real-time calculation of subtotal, discounts, tax, and grand total
 * 3. Form validation before submission
 * 4. AJAX form submission with loader feedback
 *
 * Dependencies:
 * - HTML structure must include:
 *    - A wrapper with id="items-wrapper" containing all item rows
 *    - Each row with class="item-row" and inputs:
 *        - .description   → item description (text)
 *        - .qty           → quantity (number)
 *        - .unit          → unit price (number)
 *        - .discount      → discount percentage (number)
 *        - .tax           → tax percentage (number)
 *        - .total         → calculated total (readonly)
 *        - .remove-item   → button to remove the row
 *    - Button(s) with class="add-item" to add a new row
 *    - Output fields with ids:
 *        - #subtotal, #discount-total, #tax-total, #grand-total
 *    - A hidden <input name="currency"> used to format totals
 *    - A form with id="document-form"
 *    - A submit button with:
 *        - id="submit-btn"
 *        - span#submit-label → text container
 *        - svg#spinner → loader spinner (initially hidden)
 */
document.addEventListener('DOMContentLoaded', function () {
    const trans = window.translations;
    // Shared variables
    const itemsWrapper = document.getElementById('items-wrapper');
    let itemIndex = 1;

    /**
     * Resets the submit button state: enables it, hides spinner, restores label.
     */
    function resetSubmitButtonState() {
        const submitBtn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');
        const submitLabel = document.getElementById('submit-label');

        submitBtn.disabled = false;
        spinner.classList.add('hidden');
        submitLabel.textContent = trans.save;
    }

    /**
     * Calculates totals for all item rows:
     * - Subtotal before discount/tax
     * - Total discount and tax
     * - Grand total
     */
    function calculateTotals() {
        let subtotal = 0, totalDiscount = 0, totalTax = 0, grandTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
            const unit = parseFloat(row.querySelector('.unit')?.value) || 0;
            const discount = parseFloat(row.querySelector('.discount')?.value) || 0;
            const tax = parseFloat(row.querySelector('.tax')?.value) || 0;

            const lineTotal = qty * unit;
            const lineDiscount = lineTotal * (discount / 100);
            const lineTax = (lineTotal - lineDiscount) * (tax / 100);
            const total = lineTotal - lineDiscount + lineTax;

            row.querySelector('.total').value = total.toFixed(2);

            subtotal += lineTotal;
            totalDiscount += lineDiscount;
            totalTax += lineTax;
        });

        grandTotal = subtotal - totalDiscount + totalTax;

        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('discount-total').textContent = '-' + formatCurrency(totalDiscount);
        document.getElementById('tax-total').textContent = formatCurrency(totalTax);
        document.getElementById('grand-total').textContent = formatCurrency(grandTotal);
    }

    /**
     * Formats a numeric amount into currency, using input[name="currency"]
     */
    function formatCurrency(amount) {
        const currencyInput = document.querySelector('input[name="currency"]');
        const currency = currencyInput ? currencyInput.value : '';
        return currency + parseFloat(amount).toFixed(2);
    }

    /**
     * Binds input and remove events to a row
     * @param {HTMLElement} row
     */
    function bindEvents(row) {
        row.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-item')?.addEventListener('click', () => {
            const allRows = document.querySelectorAll('.item-row');
            if (allRows.length > 1) {
                row.remove();
                calculateTotals();
            }
        });
    }

    /**
     * Handles "Add Item" button click: clones the first row, clears data, re-indexes input names
     */
    document.querySelectorAll('.add-item').forEach(btn => {
        btn.addEventListener('click', () => {
            const template = document.querySelector('.item-row');
            const newRow = template.cloneNode(true);

            newRow.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, `[${itemIndex}]`);
                    input.setAttribute('name', newName);
                }
                if (!input.readOnly) {
                    if (input.classList.contains('qty')) input.value = '1';
                    else if (input.classList.contains('unit') || input.classList.contains('discount') || input.classList.contains('tax')) input.value = '0';
                    else input.value = '';
                }
            });

            itemsWrapper.appendChild(newRow);
            bindEvents(newRow);
            itemIndex++;
            calculateTotals();
        });
    });

    /**
     * Form submission with validation and AJAX request
     */
    document.getElementById('document-form').addEventListener('submit', function (e) {
        e.preventDefault();
        let valid = true;

        const clientSelect = document.getElementById('client-select');
        const documentNumber = document.getElementById('document-number');
        const documentType = document.getElementById('document-type');
        const submitBtn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');
        const submitLabel = document.getElementById('submit-label');

        // Loader start
        submitBtn.disabled = true;
        spinner.classList.remove('hidden');
        submitLabel.textContent = trans.saving;

        // Validate static fields
        [clientSelect, documentNumber, documentType].forEach(input => {
            if (!input || input.value.trim() === '') {
                valid = false;
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        });

        // Validate item rows
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row) => {
            ['qty', 'unit', 'discount', 'tax'].forEach(className => {
                const input = row.querySelector('.' + className);
                if (!input) return;

                const value = input.value.trim();
                if (value === '' || isNaN(value)) {
                    valid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            const descriptionInput = row.querySelector('.description');
            if (!descriptionInput || descriptionInput.value.trim() === '') {
                valid = false;
                descriptionInput.classList.add('border-red-500');
            } else {
                descriptionInput.classList.remove('border-red-500');
            }
        });

        if (!valid) {
            resetSubmitButtonState();
            toastr.error(trans.errorFields);
            return;
        }

        // Send data
        const formData = new FormData(e.target);

        fetch('/documents/create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(trans.success);
                window.location.href = '/documents';
            } else {
                resetSubmitButtonState();
                toastr.error(data.error || trans.errorServer);
            }
        })
        .catch(error => {
            console.error(error);
            resetSubmitButtonState();
            toastr.error( trans.errorNetwork);
        });
    });

    // Init on load
    document.querySelectorAll('.item-row').forEach(row => bindEvents(row));
    calculateTotals();
});
