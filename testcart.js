document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-item');

    removeButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const itemRow = this.closest('[data-item-id]');
            const itemId = itemRow.getAttribute('data-item-id');

            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: itemId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    itemRow.remove();
                    // Optional: Update the summary section (items count and total price)
                } else {
                    alert('Failed to remove item from cart');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
