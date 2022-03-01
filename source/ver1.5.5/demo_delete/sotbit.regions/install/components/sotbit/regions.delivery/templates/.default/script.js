class SotbitRegionsDelivery
{
	constructor(params) {
		this.rootId = params.root;
		this.root = document.getElementById(this.rootId);
		this.componentPath = params.componentPath;
		this.parameters = params.parameters;
		this.siteId = params.siteId;
		this.template = params.template;
		this.templatePath = params.templatePath;
		this.changeRegion = this.root.querySelector('[data-entity="change-region"]');
		this.modal = this.root.querySelector('[data-entity="modal"]');
		this.overlay = this.root.querySelector('[data-entity="overlay"]');

		this.formLocation = this.root.querySelector('[data-entity="form-location"]');




		this.changeRegion.addEventListener('click',event => this.showPopup());
		if(this.overlay !== null)
		{
			this.overlay.addEventListener('click', event => this.closePopup());
		}
	}
	showPopup(){
		var xhr = new XMLHttpRequest();
		xhr.open("POST", this.templatePath+'/ajax.php', false);
		xhr.send();
		this.modal.innerHTML = xhr.responseText;
		this.root = document.getElementById(this.rootId);
		this.tab = this.root.querySelectorAll('[data-entity="tab-content"]');
		this.item = this.root.querySelectorAll('[data-entity="item"]');
		if(this.item.length > 1){
			for(let i =0; i < this.item.length;++i){
				this.item[i].addEventListener('click',event => this.chooseLocation(this.item[i].dataset.index));
			}
		}

		this.item2 = this.root.querySelectorAll('[data-entity="item2"]');
		if(this.item2.length > 1){
			for(let i =0; i < this.item2.length;++i){
				this.item2[i].addEventListener('click',event => this.chooseLocation(this.item2[i].dataset.index));
			}
		}

		this.close = this.root.querySelector('[data-entity="close"]');

		if(this.close !== null)
		{
			this.close.addEventListener('click', event => this.closePopup());
		}
		this.country = this.root.querySelectorAll('[data-entity="country"]');
		if(this.country.length > 1){
			for(let i =0; i < this.country.length;++i){
				this.country[i].addEventListener('click',event => this.chooseCountry(this.country[i].dataset.countryId));
			}
		}
		this.filter = this.root.querySelector('[data-entity="filter-regions"]');
		if(this.filter !== null)
		{
			this.filter.addEventListener('input', event => this.filterItems(this.filter.value));
		}

		this.modal.style.display = 'block';
		this.overlay.style.display = 'block';
	}
	closePopup(){
		this.modal.style.display = 'none';
		this.overlay.style.display = 'none';
	}
	chooseCountry(countryId){
		if(this.tab.length > 1){
			for(let i =0;i<this.tab.length;++i){
				this.country[i].classList.remove('active');
				this.tab[i].classList.remove('active');
				if(this.tab[i].dataset.countryId == countryId){
					this.tab[i].classList.add('active');
					this.country[i].classList.add('active');
				}
			}
		}
	}
	filterItems(value){

		let letters = this.root.querySelectorAll('[data-entity="letter"]');
		let list = this.root.querySelectorAll('[data-entity="item"]');
		value.toLowerCase();
		if(list.length){
			for(var i = 0; i < list.length; ++i){
				list[i].style.display = "block";
				let city = list[i].innerHTML.toLowerCase().trim();
				if(value.length > 0){
					if(city.substr(0,value.length) != value){
						list[i].style.display = "none";
					}
				}
			}
		}
		if(letters.length){
			for(var i = 0; i < letters.length; ++i){
				var was = false;
				var child = letters[i].childNodes;
				for(var j=0; j<child.length; ++j){
					if(child[j].dataset !== undefined && child[j].dataset.entity == 'item-list'){
						let child2 = child[j].childNodes;
						if(child2.length){
							for(var k=0; k<child2.length; ++k){
								if(child2[k].dataset !== undefined && child2[k].dataset.entity == 'item'){
									let style = getComputedStyle(child2[k]);
									if(style.display != 'none'){
										was = true;
										break;
									}
								}
							}
						}
					}
				}
				letters[i].style.display = "block";
				if(!was){
					letters[i].style.display = "none";
				}
			}
		}
	}
	chooseLocation(id){
		BX.showWait();
		var defaultData = {
			siteId: this.siteId,
			template: this.template,
			parameters: this.parameters,
		};
		var data = {
			Id: id,
		};

		BX.ajax({
			url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			method: 'POST',
			dataType: 'html',
			timeout: 60,
			data: BX.merge(defaultData, data),
			onsuccess: BX.delegate(function(result){
				this.root.outerHTML = result;
			}, this)
		});
		BX.closeWait();
	}
}