document.addEventListener('DOMContentLoaded', function() {
    // Get references to the dropdown elements
    const categoryDropdown = document.getElementById('category-dropdown');
    const subcategoryDropdown = document.getElementById('subcategory-dropdown');
    const productContainer = document.getElementById('product-container');
    
    // Add event listener to the category dropdown
    if (categoryDropdown) {
        categoryDropdown.addEventListener('change', function() {
            const selectedCategory = this.value;
            loadSubcategories(selectedCategory);
        });
    }
    
    // Add event listener to the subcategory dropdown
    if (subcategoryDropdown) {
        subcategoryDropdown.addEventListener('change', function() {
            const selectedSubcategory = this.value;
            if (selectedSubcategory) {
                loadProducts(selectedSubcategory);
            }
        });
    }
    
    // Function to load subcategories based on the selected category
    function loadSubcategories(category) {
        if (!category) return;
        
        // Clear the subcategory dropdown
        subcategoryDropdown.innerHTML = '<option value="">Select a subcategory</option>';
        
        // Show loading indication
        subcategoryDropdown.disabled = true;
        
        // Make an AJAX request to get the subcategories
        fetch(`/api/categories?category=${encodeURIComponent(category)}`)
            .then(response => response.json())
            .then(data => {
                subcategoryDropdown.disabled = false;
                
                // Populate the subcategory dropdown
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.name;
                    option.textContent = item.name;
                    option.dataset.image = item.image_url;
                    subcategoryDropdown.appendChild(option);
                });
                
                // Show the subcategory thumbnail if available
                updateSubcategoryThumbnail();
            })
            .catch(error => {
                console.error('Error loading subcategories:', error);
                subcategoryDropdown.disabled = false;
            });
    }
    
    // Function to update the subcategory thumbnail
    function updateSubcategoryThumbnail() {
        const thumbnailContainer = document.getElementById('subcategory-thumbnail');
        if (!thumbnailContainer) return;
        
        const selectedOption = subcategoryDropdown.options[subcategoryDropdown.selectedIndex];
        
        if (selectedOption && selectedOption.dataset.image) {
            const img = document.createElement('img');
            img.src = selectedOption.dataset.image;
            img.alt = selectedOption.textContent;
            img.className = 'subcategory-img';
            
            thumbnailContainer.innerHTML = '';
            thumbnailContainer.appendChild(img);
            thumbnailContainer.style.display = 'block';
        } else {
            thumbnailContainer.style.display = 'none';
        }
    }
    
    // Add event listener to update thumbnail when subcategory changes
    if (subcategoryDropdown) {
        subcategoryDropdown.addEventListener('change', updateSubcategoryThumbnail);
    }
    
    // Function to load products based on the selected subcategory
    function loadProducts(subcategory) {
        if (!subcategory) return;
        
        // Show loading indication
        if (productContainer) {
            productContainer.innerHTML = '<div class="loading">Loading products...</div>';
        }
        
        // Make an AJAX request to get the products
        fetch(`/api/products?subcategory=${encodeURIComponent(subcategory)}`)
            .then(response => response.json())
            .then(data => {
                if (productContainer) {
                    productContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        productContainer.innerHTML = '<div class="no-products">No products found</div>';
                        return;
                    }
                    
                    // Create product cards
                    data.forEach(product => {
                        const productCard = createProductCard(product);
                        productContainer.appendChild(productCard);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                if (productContainer) {
                    productContainer.innerHTML = '<div class="error">Error loading products</div>';
                }
            });
    }
    
    // Function to create a product card
    function createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        
        const isAuthenticated = document.body.dataset.authenticated === 'true';
        
        card.innerHTML = `
            <img src="${product.image_url}" alt="${product.name}">
            <div class="card-body">
                <h3>${product.name}</h3>
                <p class="price">$${parseFloat(product.price).toFixed(2)}</p>
                ${isAuthenticated ? 
                    `<button class="btn btn-primary add-to-cart" data-product-id="${product.id}" data-product-price="${product.price}">
                        Add to order
                    </button>` : 
                    '<a href="/login" class="btn btn-primary">Login to order</a>'
                }
            </div>
        `;
        
        // Add event listener to the "Add to order" button if authenticated
        if (isAuthenticated) {
            const addButton = card.querySelector('.add-to-cart');
            if (addButton) {
                addButton.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    const productPrice = this.dataset.productPrice;
                    addToOrder(productId, productPrice);
                });
            }
        }
        
        return card;
    }
    
    // Function to add a product to the order
    function addToOrder(productId, price) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('price', price);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        
        fetch('/order', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Product added to order successfully!');
                // Redirect to receipt page
                window.location.href = `/order-receipt?order_id=${data.order_id}`;
            } else {
                alert(data.error || 'Error adding product to order');
            }
        })
        .catch(error => {
            console.error('Error adding to order:', error);
            alert('Error adding product to order');
        });
    }
    
    // Load initial categories if on the catalog page
    if (categoryDropdown && categoryDropdown.options.length > 0 && categoryDropdown.value) {
        loadSubcategories(categoryDropdown.value);
    }
}); 