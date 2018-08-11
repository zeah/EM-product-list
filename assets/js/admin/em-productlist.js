(() => {

	console.log(productlist_meta);

	let newtype = '';

	// meta box
	let container = document.querySelector('.productlist-meta-container');

	// new div helper function
	let newdiv = (o = {}) => {
		let div = document.createElement('div');

		if (o.class) {
			if (Array.isArray(o.class))
				for (let c of o.class)
					div.classList.add(c);
			else
				div.classList.add(o.class);
		}

		if (o.text) div.appendChild(document.createTextNode(o.text));

		return div;
	}

	// new input helper function
	let newinput = (o = {}) => {
		if (!o.name) return document.createElement('div');

		let container = newdiv({class: 'productlist-input-container'});

		let title = newdiv({class: 'productlist-input-title', text: o.title});
		container.appendChild(title);

		let input = document.createElement('input');

		if (!o.type) input.setAttribute('type', 'text');
		else input.setAttribute('type', o.type);

		if (!o.sort) input.setAttribute('value', (productlist_meta.meta[o.name] == undefined) ? '' : productlist_meta.meta[o.name]);
		else {
			let sort = productlist_meta.productlist_sort;

			if (o.sort != 'default') sort = productlist_meta['productlist_sort_'+o.sort];

			if (sort == undefined) sort = productlist_meta.productlist_sort;

			input.setAttribute('value', sort);
		}

		if (o.step) input.setAttribute('step', parseFloat(o.step));
		if (o.max) input.setAttribute('max', parseFloat(o.step));
		if (o.min) input.setAttribute('min', parseFloat(o.step));



		if (!o.notData) input.setAttribute('name', 'productlist_data['+o.name+']');
		else input.setAttribute('name', o.name);

		container.appendChild(input);


		return container;
	}

	let newtextarea = (o = {}) => {
		if (!o.name) return;

		let container = newdiv({class: 'productlist-input-container'});

		let title = newdiv({class: 'productlist-input-title', text: o.title});
		container.appendChild(title);

		let ta = document.createElement('textarea');

		if (o.class) {
			if (Array.isArray(o.class)) 
				for (let c of o.class) 
					ta.classList.add(c);
			
			else ta.classList.add(o.class);
		}

		ta.setAttribute('name', 'productlist_data['+o.name+']');
		ta.appendChild(document.createTextNode(productlist_meta.meta[o.name]));
		// ta.setAttribute('value', (productlist_meta.meta[o.name] == undefined) ? '' : productlist_meta.meta[o.name]);

		container.appendChild(ta);

		return container;
	}

	// creating the drop down for dice selection
	let dicedropdown = (o = {}) => {
		let container = document.createElement('div');

		let input = document.createElement('select');
		input.setAttribute('name', 'productlist_data[terning]');

		container.appendChild(newdiv({class: 'productlist-input-title', text: 'Terningkast'}));

		// helper function for creating option tag
		let addOption = (o = {}) => {
			let option = document.createElement('option');
			option.setAttribute('value', o.value);
			if (o.value == productlist_meta.meta.terning) option.setAttribute('selected', '');
			option.appendChild(document.createTextNode(o.value));
			return option;
		}

		// adding option tags
		let v = ['ingen', 'en', 'to', 'tre', 'fire', 'fem', 'seks'];
		for (let i of v)
			input.appendChild(addOption({value: i}));

		// input.appendChild(addOption({value: 'ingen'}));
		// input.appendChild(addOption({value: 'en'}));
		// input.appendChild(addOption({value: 'to'}));
		// input.appendChild(addOption({value: 'tre'}));
		// input.appendChild(addOption({value: 'fire'}));
		// input.appendChild(addOption({value: 'fem'}));
		// input.appendChild(addOption({value: 'seks'}));

		container.appendChild(input);

		return container; 
	}

	let container_sort = newdiv({class: 'productlist-sort-container'});
	container_sort.appendChild(newinput({
		name: 'productlist_sort', 
		title: 'Sortering', 
		notData: true, 
		sort: 'default', 
		type: 'number',
		step: 0.01
	}));

	container.appendChild(container_sort);

	for (let sort of productlist_meta['tax'])
		container_sort.appendChild(newinput({
			name: 'productlist_sort_'+sort, 
			title: 'Sortering '+sort.replace(/-/g, ' '), 
			notData: true, 
			sort: sort, 
			type: 'number',
			step: 0.01
		}));

	// container.appendChild(newinput({name: 'readmore', title: 'Read More Link'}));

	// container.appendChild(newinput({name: 'bestill', title: 'Bestill Link'}));
	// container.appendChild(newinput({name: 'bestill_text', title: 'Bestill Text (under bestillknapp)'}));

	// let info_container = newdiv({class: 'productlist-info-container'});

	// info_container.appendChild(newinput({name: 'info01', title: 'Text 01'}));
	// info_container.appendChild(newinput({name: 'info05', title: 'Text 05'}));
	// info_container.appendChild(newinput({name: 'info02', title: 'Text 02'}));
	// info_container.appendChild(newinput({name: 'info06', title: 'Text 06'}));
	// info_container.appendChild(newinput({name: 'info03', title: 'Text 03'}));
	// info_container.appendChild(newinput({name: 'info07', title: 'Text 07'}));
	// info_container.appendChild(newinput({name: 'info04', title: 'Text 04'}));
	// info_container.appendChild(newinput({name: 'info08', title: 'Text 08'}));

	// container.appendChild(info_container);

	// container.appendChild(dicedropdown());

	container.appendChild(newtextarea({
		name: 'productprice',
		title: 'Pris',
		class: 'productlist-price'
	}));

	container.appendChild(newtextarea({
		name: 'productdescription',
		title: 'Beskrivelse'
	}));

	// adding existing category
	jQuery('#productlisttypechecklist').on('change', function(e) {

		let text = $(e.target).parent().text().trim().replace(/ /g, '-');

		if (!e.target.checked) $("input[name='productlist_sort_"+text+"']").parent().remove();
		else {
			let input = newinput({
				name: 'productlist_sort_'+text, 
				title: 'Sortering '+text.replace(/-/g, ' '), 
				notData: true, 
				sort: text, 
				type: 'number',
				step: 0.01
			});

			// $("input[name='productlist_sort']").parent().parent().append(input);
			$('.productlist-sort-container').append(input);
		}
	});

	// reading name of new category for creating
	jQuery('#newproductlisttype').on('input', function(e) { newtype = e.target.value; });

	// creating category
	jQuery('#productlisttype-add-submit').click(function(e) {
		let text = newtype.trim().replace(/ /g, '-');
		let input = newinput({name: 'productlist_sort_'+text, title: 'Sortering '+text.replace(/-/g, ' '), notData: true, sort: text, type: 'number'});
		$('.productlist-sort-container').append(input);
		// $("input[name='productlist_sort']").parent().parent().append(input);
	});

})();