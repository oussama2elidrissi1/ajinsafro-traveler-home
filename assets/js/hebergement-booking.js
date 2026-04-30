(function () {
  'use strict';

  var root = document.getElementById('aj-hebergement-booking');
  if (!root) {
    return;
  }

  var config = typeof window.ajthHebergementConfig === 'object' && window.ajthHebergementConfig
    ? window.ajthHebergementConfig
    : {};

  var currency = config.currency || 'DH';
  var strings = config.strings || {};

  var amenityLabels = {
    wifi: 'Wi-Fi',
    pool: 'Piscine',
    parking: 'Parking',
    air_conditioning: 'Climatisation',
    breakfast: 'Petit-déjeuner',
    restaurant: 'Restaurant',
    spa: 'Spa',
    gym: 'Salle de sport',
    sea_view: 'Vue mer',
    family: 'Chambre familiale',
    transfer: 'Transfert',
    activity: 'Activité',
    assistance: 'Assistance Ajinsafro',
    half_board: 'Demi-pension',
    full_board: 'Pension complète'
  };

  var packAmenityMap = {
    hebergement: null,
    breakfast: 'breakfast',
    'petit-dejeuner': 'breakfast',
    'petit-dejeuner-inclus': 'breakfast',
    'demi-pension': 'half_board',
    'pension-complete': 'full_board',
    transfert: 'transfer',
    'transfert-optionnel': 'transfer',
    'activite-optionnelle': 'activity',
    'offre-famille': 'family',
    'conseils-locaux': 'assistance',
    'guide-optionnel': 'assistance',
    'assistance-ajinsafro': 'assistance',
    'support-reservation': 'assistance'
  };

  function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatPrice(value) {
    if (value === null || value === undefined || value === '' || Number.isNaN(Number(value))) {
      return 'Sur demande';
    }
    return Number(value).toLocaleString('fr-FR') + ' ' + currency;
  }

  function renderStars(count) {
    var c = Number(count || 0);
    if (c <= 0) {
      return '<span class="aj-star-line aj-star-line--empty">Type libre</span>';
    }
    var html = '<span class="aj-star-line" aria-label="' + c + ' étoiles">';
    for (var i = 0; i < c; i++) {
      html += '<span aria-hidden="true">★</span>';
    }
    html += '</span>';
    return html;
  }

  function truncateText(value, maxLength) {
    var text = String(value || '').replace(/\s+/g, ' ').trim();
    if (!text || text.length <= maxLength) {
      return text;
    }
    return text.slice(0, maxLength).replace(/[.,;:!?-]?\s+\S*$/, '').trim() + '...';
  }

  function normalizeItem(raw) {
    var item = raw && typeof raw === 'object' ? raw : {};
    var isPack = item.kind === 'pack';

    var location = '';
    if (isPack) {
      location = [item.city, item.country].filter(Boolean).join(', ') || 'Maroc';
    } else {
      location = String(item.location || item.city || item.address || '');
    }

    var amenities = [];
    if (isPack && Array.isArray(item.includes)) {
      item.includes.forEach(function (inc) {
        var key = packAmenityMap[String(inc).toLowerCase().trim()];
        if (key && !amenities.includes(key)) {
          amenities.push(key);
        }
      });
    } else if (Array.isArray(item.amenities)) {
      amenities = item.amenities.filter(Boolean);
    }

    var description = String(item.description || item.excerpt || item.short_description || '');
    if (!description && isPack) {
      description = 'Séjour Ajinsafro prêt à réserver avec les essentiels déjà inclus.';
    } else if (!description) {
      description = 'Hébergement Ajinsafro disponible dans notre catalogue.';
    }

    return {
      isPack: isPack,
      id: Number(item.id || 0),
      title: String(item.title || item.name || ''),
      url: String(item.url || '#'),
      image: String(item.image || item.image_url || ''),
      location: location,
      category: String(isPack ? (item.typeLabel || 'Hôtel') : (item.category || 'Hôtel')),
      type: String(isPack ? (item.type || 'hotel') : (item.type || 'hotel')),
      stars: Number(isPack ? 0 : (item.stars || 0)),
      price: item.price !== null && item.price !== undefined && item.price !== '' ? Number(item.price) : null,
      oldPrice: item.oldPrice !== null && item.oldPrice !== undefined && item.oldPrice !== '' ? Number(item.oldPrice) : null,
      description: description,
      amenities: amenities,
      popular: Boolean(item.popular || item.is_featured || item.featured),
      available: item.available !== false,
      discount: Number(item.discount || 0),
      rating: item.rating !== null && item.rating !== undefined && item.rating !== '' ? Number(item.rating) : null,
      reviews: Number(item.reviews || 0),
      badge: String(isPack ? (item.badges && item.badges[0] ? item.badges[0] : '') : (item.badge || '')),
      duration: String(isPack ? (item.duration || '') : ''),
      boardLabel: String(isPack ? (item.pensionLabel || '') : (item.boardLabel || item.board || '')),
      order: Number(item.order || 0)
	    };
  }

  var rawHotels = Array.isArray(config.hotels) ? config.hotels : [];
  var rawPacks = Array.isArray(config.packs) ? config.packs : [];
  var allItems = rawHotels.map(normalizeItem).concat(rawPacks.map(normalizeItem));

  // Extract unique values for selects
  var destinations = Array.from(new Set(allItems.map(function (i) { return i.location; }).filter(Boolean))).sort(function (a, b) { return a.localeCompare(b, 'fr', { sensitivity: 'base' }); });
  var types = Array.from(new Set(allItems.map(function (i) { return i.category; }).filter(Boolean))).sort(function (a, b) { return a.localeCompare(b, 'fr', { sensitivity: 'base' }); });

  var state = {
    hasSearched: false,
    destination: '',
    date: '',
    type: '',
    stars: [],
    nameQuery: '',
    filterDestination: '',
    filterType: '',
    minPrice: '',
    maxPrice: '',
    popularOnly: false,
    availableOnly: false,
    promoOnly: false,
    services: [],
    sort: 'recommended'
  };

  var els = {
    featuredGrid: root.querySelector('#ajhb-featured-grid'),
    catalogSection: root.querySelector('#ajhb-catalog-section'),
    resultsGrid: root.querySelector('#ajhb-results-grid'),
    resultsCount: root.querySelector('#ajhb-count'),
    activeFilters: root.querySelector('#ajhb-active-filters'),
    emptyState: root.querySelector('#ajhb-empty-state'),
    emptyReset: root.querySelector('#ajhb-empty-reset'),
    sortSelect: root.querySelector('#ajhb-sort-select'),
    searchForm: root.querySelector('#ajhb-search-form'),
    heroDestination: root.querySelector('#ajhb-destination'),
    heroDate: root.querySelector('#ajhb-date'),
    heroType: root.querySelector('#ajhb-type'),
    heroStars: root.querySelector('#ajhb-stars'),
    filterName: root.querySelector('#ajhb-filter-name'),
    filterDestination: root.querySelector('#ajhb-filter-destination'),
    filterType: root.querySelector('#ajhb-filter-type'),
    filterStars: root.querySelector('#ajhb-filter-stars'),
    filterPriceMin: root.querySelector('#ajhb-filter-price-min'),
    filterPriceMax: root.querySelector('#ajhb-filter-price-max'),
    filterPopular: root.querySelector('#ajhb-filter-popular'),
    filterAvailable: root.querySelector('#ajhb-filter-available'),
    filterPromo: root.querySelector('#ajhb-filter-promo'),
    filterWifi: root.querySelector('#ajhb-filter-wifi'),
    filterPool: root.querySelector('#ajhb-filter-pool'),
    filterParking: root.querySelector('#ajhb-filter-parking'),
    filterBreakfast: root.querySelector('#ajhb-filter-breakfast'),
    filterAc: root.querySelector('#ajhb-filter-ac'),
    resetFilters: root.querySelector('#ajhb-reset-filters'),
    mobileName: root.querySelector('#ajhb-mobile-name'),
    mobileDestination: root.querySelector('#ajhb-mobile-destination'),
    mobileType: root.querySelector('#ajhb-mobile-type'),
    mobileStars: root.querySelector('#ajhb-mobile-stars'),
    mobilePriceMin: root.querySelector('#ajhb-mobile-price-min'),
    mobilePriceMax: root.querySelector('#ajhb-mobile-price-max'),
    mobilePopular: root.querySelector('#ajhb-mobile-popular'),
    mobileAvailable: root.querySelector('#ajhb-mobile-available'),
    mobilePromo: root.querySelector('#ajhb-mobile-promo'),
    mobileWifi: root.querySelector('#ajhb-mobile-wifi'),
    mobilePool: root.querySelector('#ajhb-mobile-pool'),
    mobileParking: root.querySelector('#ajhb-mobile-parking'),
    mobileBreakfast: root.querySelector('#ajhb-mobile-breakfast'),
    mobileAc: root.querySelector('#ajhb-mobile-ac'),
    openMobileFilters: root.querySelector('#ajhb-open-filters'),
    closeMobileFilters: root.querySelector('#ajhb-close-mobile-filters'),
    applyMobileFilters: root.querySelector('#ajhb-apply-mobile-filters'),
    resetMobileFilters: root.querySelector('#ajhb-reset-mobile-filters'),
    mobilePanel: root.querySelector('#ajhb-mobile-panel'),
    mobileBackdrop: root.querySelector('#ajhb-mobile-backdrop')
  };

  if (!els.featuredGrid) {
    return;
  }

  // Hydrate selects with data-derived options
  function fillSelect(select, values, placeholder) {
    if (!select) return;
    var options = ['<option value="">' + escapeHtml(placeholder) + '</option>'];
    values.forEach(function (v) {
      options.push('<option value="' + escapeHtml(v) + '">' + escapeHtml(v) + '</option>');
    });
    select.innerHTML = options.join('');
  }

  fillSelect(els.filterDestination, destinations, 'Toutes les destinations');
  fillSelect(els.mobileDestination, destinations, 'Toutes les destinations');
  fillSelect(els.filterType, types, 'Tous les types');
  fillSelect(els.mobileType, types, 'Tous les types');

  // Initialize from URL params
  function initFromUrl() {
    var params = new URLSearchParams(window.location.search);
    var dest = params.get('destination') || '';
    var date = params.get('date') || '';
    var type = params.get('type') || '';
    var stars = params.get('stars') || '';

    if (dest || date || type || stars) {
      state.hasSearched = true;
      state.destination = dest;
      state.date = date;
      state.type = type;
      if (stars) {
        state.stars = [stars];
      }
    }
  }

  initFromUrl();

  function syncAllControlsFromState() {
    if (els.heroDestination) els.heroDestination.value = state.destination;
    if (els.heroDate) els.heroDate.value = state.date;
    if (els.heroType) els.heroType.value = state.type;
    if (els.heroStars) els.heroStars.value = state.stars[0] || '';

    if (els.filterName) els.filterName.value = state.nameQuery;
    if (els.filterDestination) els.filterDestination.value = state.filterDestination;
    if (els.filterType) els.filterType.value = state.filterType;
    if (els.filterPriceMin) els.filterPriceMin.value = state.minPrice;
    if (els.filterPriceMax) els.filterPriceMax.value = state.maxPrice;
    if (els.filterPopular) els.filterPopular.checked = state.popularOnly;
    if (els.filterAvailable) els.filterAvailable.checked = state.availableOnly;
    if (els.filterPromo) els.filterPromo.checked = state.promoOnly;

    if (els.filterStars) {
      els.filterStars.querySelectorAll('input').forEach(function (input) {
        input.checked = state.stars.includes(input.value);
      });
    }

    if (els.filterWifi) els.filterWifi.checked = state.services.includes('wifi');
    if (els.filterPool) els.filterPool.checked = state.services.includes('pool');
    if (els.filterParking) els.filterParking.checked = state.services.includes('parking');
    if (els.filterBreakfast) els.filterBreakfast.checked = state.services.includes('breakfast');
    if (els.filterAc) els.filterAc.checked = state.services.includes('air_conditioning');

    if (els.sortSelect) els.sortSelect.value = state.sort;

    syncMobileControls();
  }

  function syncMobileControls() {
    if (els.mobileName) els.mobileName.value = state.nameQuery;
    if (els.mobileDestination) els.mobileDestination.value = state.filterDestination;
    if (els.mobileType) els.mobileType.value = state.filterType;
    if (els.mobilePriceMin) els.mobilePriceMin.value = state.minPrice;
    if (els.mobilePriceMax) els.mobilePriceMax.value = state.maxPrice;
    if (els.mobilePopular) els.mobilePopular.checked = state.popularOnly;
    if (els.mobileAvailable) els.mobileAvailable.checked = state.availableOnly;
    if (els.mobilePromo) els.mobilePromo.checked = state.promoOnly;

    if (els.mobileStars) {
      els.mobileStars.querySelectorAll('input').forEach(function (input) {
        input.checked = state.stars.includes(input.value);
      });
    }

    if (els.mobileWifi) els.mobileWifi.checked = state.services.includes('wifi');
    if (els.mobilePool) els.mobilePool.checked = state.services.includes('pool');
    if (els.mobileParking) els.mobileParking.checked = state.services.includes('parking');
    if (els.mobileBreakfast) els.mobileBreakfast.checked = state.services.includes('breakfast');
    if (els.mobileAc) els.mobileAc.checked = state.services.includes('air_conditioning');
  }

  function readServicesFromContainer(container) {
    var services = [];
    var checks = [
      { el: container.querySelector('[value="wifi"]'), key: 'wifi' },
      { el: container.querySelector('[value="pool"]'), key: 'pool' },
      { el: container.querySelector('[value="parking"]'), key: 'parking' },
      { el: container.querySelector('[value="breakfast"]'), key: 'breakfast' },
      { el: container.querySelector('[value="air_conditioning"]'), key: 'air_conditioning' }
    ];
    checks.forEach(function (c) {
      if (c.el && c.el.checked) {
        services.push(c.key);
      }
    });
    return services;
  }

  function readStarsFromContainer(container) {
    if (!container) return [];
    var stars = [];
    container.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
      if (input.checked) {
        stars.push(input.value);
      }
    });
    return stars;
  }

  function matchesFilters(item) {
    var searchable = [item.title, item.location, item.category, item.type, item.description].join(' ').toLowerCase();

    if (state.destination) {
      var dest = state.destination.toLowerCase().trim();
      if (searchable.indexOf(dest) === -1 && item.location.toLowerCase().indexOf(dest) === -1) {
        return false;
      }
    }

    if (state.filterDestination && item.location !== state.filterDestination) {
      return false;
    }

    if (state.nameQuery) {
      var q = state.nameQuery.toLowerCase().trim();
      if (searchable.indexOf(q) === -1) {
        return false;
      }
    }

    if (state.type && item.type !== state.type && item.category !== state.type) {
      return false;
    }

    if (state.filterType && item.category !== state.filterType && item.type !== state.filterType) {
      return false;
    }

    if (state.stars.length) {
      var starMatch = false;
      for (var i = 0; i < state.stars.length; i++) {
        if (String(item.stars) === String(state.stars[i])) {
          starMatch = true;
          break;
        }
      }
      if (!starMatch) return false;
    }

    if (state.minPrice && (item.price === null || item.price < Number(state.minPrice))) {
      return false;
    }
    if (state.maxPrice && (item.price === null || item.price > Number(state.maxPrice))) {
      return false;
    }

    if (state.popularOnly && !item.popular) {
      return false;
    }
    if (state.availableOnly && !item.available) {
      return false;
    }
    if (state.promoOnly && !(item.discount || item.oldPrice)) {
      return false;
    }

    if (state.services.length) {
      for (var s = 0; s < state.services.length; s++) {
        if (!item.amenities.includes(state.services[s])) {
          return false;
        }
      }
    }

    return true;
  }

  function sortItems(list) {
    var sorted = list.slice();
    if (state.sort === 'price-asc') {
      sorted.sort(function (a, b) { return (a.price || 0) - (b.price || 0); });
    } else if (state.sort === 'price-desc') {
      sorted.sort(function (a, b) { return (b.price || 0) - (a.price || 0); });
    } else if (state.sort === 'rating-desc') {
      sorted.sort(function (a, b) { return (b.rating || 0) - (a.rating || 0); });
    } else if (state.sort === 'stars-desc') {
      sorted.sort(function (a, b) { return (b.stars || 0) - (a.stars || 0); });
    } else {
      // recommended
      sorted.sort(function (a, b) {
        return (Number(!!b.popular) - Number(!!a.popular))
          || ((b.rating || 0) - (a.rating || 0))
          || ((b.stars || 0) - (a.stars || 0))
          || ((a.price || 0) - (b.price || 0));
      });
    }
    return sorted;
  }
  function renderFeaturedCards() {
    var featured = allItems.filter(function (item) { return item.isPack && item.popular; });
    featured.sort(function (a, b) { return (a.order || 99) - (b.order || 99); });
    if (!featured.length) {
      els.featuredGrid.innerHTML = '';
      return;
    }
    els.featuredGrid.innerHTML = featured.map(renderFeaturedCard).join('');
  }

  function renderFeaturedCard(item) {
    var image = item.image
      ? '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.title) + '" loading="lazy">'
      : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#d9e9ff,#f4f8ff);color:#67809a;font-size:13px;font-weight:600;">Ajinsafro</div>';

    var badge = 'Pack';
    var priceHtml = item.price !== null
      ? '<div class="aj-featured-price"><small>À partir de</small>' + escapeHtml(formatPrice(item.price)) + '</div>'
      : '';

    var typeLabel = item.category || 'Hébergement';
    var starsHtml = item.stars > 0 ? renderStars(item.stars) : '';

    return '' +
      '<article class="aj-featured-card">' +
        '<div class="aj-featured-visual">' +
          image +
          '<span class="aj-badge">' + escapeHtml(badge) + '</span>' +
          priceHtml +
        '</div>' +
        '<div class="aj-featured-content">' +
          '<div class="aj-inline-meta">' +
            '<span>' + escapeHtml(item.location || 'Maroc') + '</span>' +
            '<span>' + escapeHtml(typeLabel) + '</span>' +
          '</div>' +
          '<h3>' + escapeHtml(item.title) + '</h3>' +
          (starsHtml ? '<div>' + starsHtml + '</div>' : '') +
          '<p style="margin:0;color:var(--aj-muted);font-size:13px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + escapeHtml(truncateText(item.description, 90)) + '</p>' +
          '<a class="aj-featured-link" href="' + escapeHtml(item.url) + '">Voir le pack</a>' +
        '</div>' +
      '</article>';
  }

  function renderResultCard(item) {
    var image = item.image
      ? '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.title) + '" loading="lazy">'
      : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#d9e9ff,#f4f8ff);color:#67809a;font-size:13px;font-weight:600;">Ajinsafro</div>';

    var badge = item.badge || (item.isPack ? 'Pack' : '');
    var categoryBadge = '<span class="aj-category-badge">' + escapeHtml(item.category) + '</span>';
    var statusBadge = item.available
      ? '<span class="aj-status-badge">Disponible</span>'
      : '<span class="aj-status-badge aj-status-badge--unavailable">Complet</span>';

    var facts = [];
    if (item.boardLabel) facts.push(item.boardLabel);
    item.amenities.slice(0, 3).forEach(function (a) {
      var label = amenityLabels[a] || a;
      if (label) facts.push(label);
    });
    var factsHtml = facts.length
      ? '<div class="aj-result-facts">' + facts.map(function (f) { return '<span class="aj-result-fact">' + escapeHtml(f) + '</span>'; }).join('') + '</div>'
      : '';

    var priceHtml = '';
    if (item.price !== null) {
      priceHtml = '<div class="aj-result-price">' +
        '<small>À partir de</small>' +
        '<div>' +
          (item.oldPrice ? '<span class="aj-old-price">' + escapeHtml(formatPrice(item.oldPrice)) + '</span>' : '') +
          '<strong>' + escapeHtml(formatPrice(item.price)) + '</strong>' +
        '</div>' +
      '</div>';
    } else {
      priceHtml = '<div class="aj-result-price"><strong>Sur demande</strong></div>';
    }

    return '' +
      '<article class="aj-result-card">' +
        '<div class="aj-result-media">' +
          image +
          '<div class="aj-card-badges">' +
            categoryBadge +
            statusBadge +
          '</div>' +
        '</div>' +
        '<div class="aj-result-body">' +
          '<div class="aj-result-meta">' +
            escapeHtml(item.location || 'Maroc') +
            ' · ' + renderStars(item.stars) +
            (item.rating ? ' · <span class="aj-rating"><strong>' + item.rating.toFixed(1) + '</strong></span>' : '') +
          '</div>' +
          '<h3><a href="' + escapeHtml(item.url) + '">' + escapeHtml(item.title) + '</a></h3>' +
          '<p class="aj-result-desc">' + escapeHtml(truncateText(item.description, 140)) + '</p>' +
          factsHtml +
          '<div class="aj-result-footer">' +
            priceHtml +
            '<a class="aj-result-btn" href="' + escapeHtml(item.url) + '">Voir l\'hébergement</a>' +
          '</div>' +
        '</div>' +
      '</article>';
  }

  function renderActiveFilterChips() {
    var chips = [];
    if (state.destination) chips.push('Destination: ' + state.destination);
    if (state.filterDestination) chips.push(state.filterDestination);
    if (state.nameQuery) chips.push('Recherche: ' + state.nameQuery);
    if (state.type) chips.push('Type: ' + state.type);
    if (state.filterType) chips.push('Type: ' + state.filterType);
    state.stars.forEach(function (s) { chips.push(s + ' étoile' + (s > 1 ? 's' : '')); });
    if (state.minPrice) chips.push('Min ' + state.minPrice + ' ' + currency);
    if (state.maxPrice) chips.push('Max ' + state.maxPrice + ' ' + currency);
    if (state.popularOnly) chips.push('Sélection Ajinsafro');
    if (state.availableOnly) chips.push('Disponible');
    if (state.promoOnly) chips.push('Promotions');
    state.services.forEach(function (s) {
      var label = amenityLabels[s] || s;
      chips.push(label);
    });

    els.activeFilters.innerHTML = chips.map(function (label) {
      return '<span class="aj-filter-chip">' + escapeHtml(label) + '<button type="button" data-ajhb-remove="all">×</button></span>';
    }).join('');
  }

  function renderCatalog() {
    if (!state.hasSearched) {
      if (els.catalogSection) els.catalogSection.hidden = true;
      return;
    }

    if (els.catalogSection) els.catalogSection.hidden = false;

    var filtered = allItems.filter(matchesFilters);
    var sorted = sortItems(filtered);

    if (els.resultsCount) els.resultsCount.textContent = String(sorted.length);
    renderActiveFilterChips();

    if (els.resultsGrid) {
      els.resultsGrid.innerHTML = sorted.map(renderResultCard).join('');
    }

    if (els.emptyState) {
      els.emptyState.hidden = sorted.length > 0;
    }
  }

  function applyHeroSearch() {
    state.hasSearched = true;
    state.destination = els.heroDestination ? els.heroDestination.value.trim() : '';
    state.date = els.heroDate ? els.heroDate.value : '';
    state.type = els.heroType ? els.heroType.value : '';
    var starsVal = els.heroStars ? els.heroStars.value : '';
    state.stars = starsVal ? [starsVal] : [];
    syncAllControlsFromState();
    renderCatalog();
  }

  function applyDesktopFilters() {
    state.nameQuery = els.filterName ? els.filterName.value.trim() : '';
    state.filterDestination = els.filterDestination ? els.filterDestination.value : '';
    state.filterType = els.filterType ? els.filterType.value : '';
    state.stars = readStarsFromContainer(els.filterStars);
    state.minPrice = els.filterPriceMin ? els.filterPriceMin.value : '';
    state.maxPrice = els.filterPriceMax ? els.filterPriceMax.value : '';
    state.popularOnly = els.filterPopular ? els.filterPopular.checked : false;
    state.availableOnly = els.filterAvailable ? els.filterAvailable.checked : false;
    state.promoOnly = els.filterPromo ? els.filterPromo.checked : false;
    state.services = readServicesFromContainer(els.filterStars ? els.filterStars.parentElement.parentElement : null);
    // Read services from the services group which is after availability group
    // Actually services are in the same container, just find them
    var filterCard = root.querySelector('#ajhb-desktop-filters .aj-filter-card');
    if (filterCard) {
      state.services = readServicesFromContainer(filterCard);
    }
    state.hasSearched = true;
    syncAllControlsFromState();
    renderCatalog();
  }

  function applyMobileFilters() {
    state.nameQuery = els.mobileName ? els.mobileName.value.trim() : '';
    state.filterDestination = els.mobileDestination ? els.mobileDestination.value : '';
    state.filterType = els.mobileType ? els.mobileType.value : '';
    state.stars = readStarsFromContainer(els.mobileStars);
    state.minPrice = els.mobilePriceMin ? els.mobilePriceMin.value : '';
    state.maxPrice = els.mobilePriceMax ? els.mobilePriceMax.value : '';
    state.popularOnly = els.mobilePopular ? els.mobilePopular.checked : false;
    state.availableOnly = els.mobileAvailable ? els.mobileAvailable.checked : false;
    state.promoOnly = els.mobilePromo ? els.mobilePromo.checked : false;
    var mobilePanel = els.mobilePanel;
    if (mobilePanel) {
      state.services = readServicesFromContainer(mobilePanel);
    }
    state.hasSearched = true;
    syncAllControlsFromState();
    renderCatalog();
    closeMobileFilters();
  }

  function resetState() {
    state.hasSearched = false;
    state.destination = '';
    state.date = '';
    state.type = '';
    state.stars = [];
    state.nameQuery = '';
    state.filterDestination = '';
    state.filterType = '';
    state.minPrice = '';
    state.maxPrice = '';
    state.popularOnly = false;
    state.availableOnly = false;
    state.promoOnly = false;
    state.services = [];
    state.sort = 'recommended';
    syncAllControlsFromState();
    renderCatalog();
  }

  function openMobileFilters() {
    syncMobileControls();
    if (els.mobilePanel) els.mobilePanel.classList.add('is-active');
    if (els.mobileBackdrop) els.mobileBackdrop.classList.add('is-active');
    document.body.classList.add('aj-mobile-filters-open');
  }

  function closeMobileFilters() {
    if (els.mobilePanel) els.mobilePanel.classList.remove('is-active');
    if (els.mobileBackdrop) els.mobileBackdrop.classList.remove('is-active');
    document.body.classList.remove('aj-mobile-filters-open');
  }

  function bindEvents() {
    if (els.searchForm) {
      els.searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        applyHeroSearch();
      });
    }

    if (els.sortSelect) {
      els.sortSelect.addEventListener('change', function () {
        state.sort = els.sortSelect.value;
        renderCatalog();
      });
    }

    if (els.resetFilters) {
      els.resetFilters.addEventListener('click', resetState);
    }
    if (els.emptyReset) {
      els.emptyReset.addEventListener('click', resetState);
    }

    // Desktop filter listeners (input/change on the filters container)
    var desktopFilters = root.querySelector('#ajhb-desktop-filters');
    if (desktopFilters) {
      desktopFilters.addEventListener('input', function (event) {
        if (event.target.closest('.aj-filter-card')) {
          applyDesktopFilters();
        }
      });
      desktopFilters.addEventListener('change', function (event) {
        if (event.target.closest('.aj-filter-card')) {
          applyDesktopFilters();
        }
      });
    }

    // Mobile panel listeners
    if (els.openMobileFilters) {
      els.openMobileFilters.addEventListener('click', openMobileFilters);
    }
    if (els.closeMobileFilters) {
      els.closeMobileFilters.addEventListener('click', closeMobileFilters);
    }
    if (els.mobileBackdrop) {
      els.mobileBackdrop.addEventListener('click', closeMobileFilters);
    }
    if (els.applyMobileFilters) {
      els.applyMobileFilters.addEventListener('click', applyMobileFilters);
    }
    if (els.resetMobileFilters) {
      els.resetMobileFilters.addEventListener('click', function () {
        resetState();
        closeMobileFilters();
      });
    }

    // Active filter chips remove-all
    if (els.activeFilters) {
      els.activeFilters.addEventListener('click', function (event) {
        if (event.target.closest('[data-ajhb-remove]')) {
          resetState();
        }
      });
    }

    // Escape key
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && els.mobilePanel && els.mobilePanel.classList.contains('is-active')) {
        closeMobileFilters();
      }
    });
  }

  // Initial render
  syncAllControlsFromState();
  renderFeaturedCards();
  renderCatalog();
  bindEvents();
})();
