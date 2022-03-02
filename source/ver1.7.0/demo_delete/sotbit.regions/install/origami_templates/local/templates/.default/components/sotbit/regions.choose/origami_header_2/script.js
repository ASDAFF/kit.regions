;
class RegionsChoose {

    constructor() {

        this.getRegion();

        this.rootId = 'regions_choose_component';
        this.rootDropDownId = 'regions_choose_component_dropdown';
        this.selectRegionID = 'regon_choose_select-city__modal';
        this.selectRegionOverlayID = 'regon_choose_modal__overlay';

        this.questionRegionId = null;

        this.loctionList = null;

        this.bigCityList = null;

        this.countryList = null;

        this.activCountry = null;

        this.defoultExampleRegion = null;

        this.root = document.getElementById(this.rootId);
        this.rootDropDown = document.getElementById(this.rootDropDownId);
        this.selectRegion = document.getElementById(this.selectRegionID);
        this.selectRegionOverlay = document.getElementById(this.selectRegionOverlayID);

        this.setEvents();
    }

    getEntity(parent, entity, all=false) {
        if (!parent || !entity) {
            return null;
        }
        if (all) {
            return parent.querySelectorAll('[data-entity="' + entity + '"]');
        }
        return parent.querySelector('[data-entity="' + entity + '"]');
    }

    setEvents() {
        const yesBtn = this.getEntity(this.rootDropDown, 'select-city__dropdown__choose__yes');
        const notBnt = this.getEntity(this.rootDropDown, 'select-city__dropdown__choose__no');
        const cityName = this.getEntity(this.root, 'select-city__block__text-city');
        const selectRegionClose = this.getEntity(this.selectRegion, 'select-city__close');
        const searchLine = this.getEntity(this.selectRegion, 'select-city__modal__submit__input');

        this.defoultExampleRegion = this.getEntity(this.selectRegion, 'select-city__input__example').cloneNode(true);

        yesBtn.addEventListener('click', () => this.onSetRegion(this.questionRegionId));
        notBnt.addEventListener('click', () => {
            this.onShowALLRegions();
            this.dropDownShow(false);
        });
        cityName.addEventListener('click', () => this.onShowALLRegions());
        selectRegionClose.addEventListener('click', () => this.onSelectRegionShow(false));
        searchLine.addEventListener('input', e => this.onInputSearch(e));

    }

    onSetRegion(regionId) {
        BX.ajax.runAction('sotbit:regions.ChooseComponentController.setRegion', {
            data: {regionId: regionId},
        }).then(
            (res) => res.data.actions.forEach(i => this[i](res.data)),
            (err) => {console.log(err)},
        )
    }

    dropDownShow(action) {
        if (action) {
            this.rootDropDown.setAttribute('style', 'display: block;');
        } else {
            this.rootDropDown.setAttribute('style', 'display: none;');
        }
    }

    onShowALLRegions(countryId=Number.MAX_SAFE_INTEGER) {

        if (this.loctionList !== null && countryId === Number.MAX_SAFE_INTEGER) {
            return this.SHOW_SELECT_REGIONS({
                allRegions: this.loctionList,
                locationTemplateData: {activ: this.activCountry}
            });
        }

        BX.ajax.runAction('sotbit:regions.ChooseComponentController.showLocations', {
            data: {countryId}
        })
            .then(
                (res) => res.data.actions.forEach(i => this[i](res.data)),
                (err) => console.log(err),
            )
    }

    getRegion(deleteCookies=false) {

        const query = new URLSearchParams(window.location.search);

        const params = deleteCookies
            ? {deletCookies: 1}
            : {redirectRegionId: query.get('redirectRegionId')}

        this.removeRegionGetParams(query);

        BX.ajax.runAction('sotbit:regions.ChooseComponentController.getRegion', {
            data: params,
        })
        .then(
            (res) => res.data.actions.forEach(i => this[i](res.data)),
            (err) => console.log(err),
        )

    }

    onSelectRegionShow(action) {
        if (action) {
            this.selectRegion.setAttribute('style', 'display: block;');
            this.selectRegionOverlay.setAttribute('style', 'display: block;');
        } else {
            this.selectRegion.setAttribute('style', 'display: none;');
            this.selectRegionOverlay.setAttribute('style', 'display: none;');
        }
    }

    SHOW_REGION_NAME ({currentRegionName}) {
        const elment = this.getEntity(this.root, 'select-city__block__text-city');
        elment.innerText = currentRegionName;
    }

    SHOW_QUESTION ({currentRegionName, currentRegionId}) {
        this.questionRegionId = currentRegionId;
        this.dropDownShow(true);
        const element = this.getEntity(this.rootDropDown, 'select-city__dropdown__title');
        element.innerText = element.textContent.replace('###', currentRegionName).trim();
    }

    CONFIRM_DOMAIN ({currentRegionName}) {
        this.dropDownShow(false);
        const elemnt = this.getEntity(this.root, 'select-city__block__text-city');
        elemnt.innerText = currentRegionName;
        this.getEntity(this.selectRegion, 'select-city__js').innerText = currentRegionName;
    }

    SHOW_SELECT_REGIONS ({allRegions, locationTemplateData}) {

        this.onSelectRegionShow(true);

        if (locationTemplateData === null) {
            locationTemplateData = {
                bigCity: allRegions,
                country: {},
                activ: 0,
            };
        }

        if (this.activCountry === locationTemplateData['activ']) {
            return;
        }

        this.loctionList = allRegions;
        this.countryList = locationTemplateData['country'];
        this.activCountry = locationTemplateData['activ'];
        this.bigCityList = locationTemplateData['bigCity'];

        this.citysRender();
        this.renderCountryTabs();
    }

    REDIRECT_TO_SUBDOMAIN ({currentRegionCode, currentRegionId}) {
        const hostName = window.location.hostname;
        const protocol = window.location.protocol;
        const newUrl = window.location.href.replace(hostName, currentRegionCode);
        const url = new URL(newUrl, `${protocol}${currentRegionCode}`);
        url.searchParams.set('redirectRegionId', currentRegionId);
        window.location.href = url.toString();
    }

    onInputSearch(e) {
        this.searchLine = e.currentTarget.value;
        this.citysRender();
    }

    citysRender() {

        const bitCityWraper = this.getEntity(
            this.getEntity(this.selectRegion, 'select-city__list_wrapper_favorites'),
            'select-city__list',
        );
        const litersWrapper = this.getEntity(this.selectRegion, 'select-city__list_wrapper_cities');
        const exapmleRegions = this.getEntity(this.selectRegion, 'select-city__input__example');

        Array.from(bitCityWraper.childNodes).forEach(i => i.remove());
        Array.from(litersWrapper.childNodes).forEach(i => i.remove());
        Array.from(exapmleRegions.childNodes).forEach(i => i.remove());
        exapmleRegions.innerHTML = this.defoultExampleRegion.innerHTML;

        if (!!this.bigCityList && Object.keys(this.bigCityList).length !== 0) {
            this.getEntity(this.selectRegion, 'select-city__tab_name_content__big_city')
                .setAttribute('style', 'display: block;');
        } else {
            this.getEntity(this.selectRegion, 'select-city__tab_name_content__big_city')
                .setAttribute('style', 'display: none;');
        }

        Object.keys(this.bigCityList)
            .filter(i => new RegExp(this.searchLine, 'i').test(this.bigCityList[i]))
            .forEach(i => {
                const element = document.createElement('p');
                element.setAttribute('class', 'select-city__list_item');
                element.innerText = this.bigCityList[i];
                element.addEventListener('click', () => {
                    this.onSetRegion(i);
                });
                bitCityWraper.append(element);
            });

        const liters = new Set(
            Object.values(this.loctionList)
                .filter(i => new RegExp(this.searchLine, 'i').test(i))
                .map(i => i[0])
        );

        Array.from(liters).forEach(i => {
            const oneliterWrapper = document.createElement('div')
            oneliterWrapper.setAttribute('class', 'select-city__list_letter_wrapper');

            const literElem = document.createElement('div');
            literElem.setAttribute('class', 'select-city__list_letter');
            literElem.innerText = i;
            oneliterWrapper.append(literElem);

            const citysWrapper = document.createElement('div');
            citysWrapper.setAttribute('class', 'select-city__list_1');
            Object.keys(this.loctionList)
                .filter(j => i === this.loctionList[j][0])
                .filter(i => new RegExp(this.searchLine, 'i').test(this.loctionList[i]))
                .forEach(cityId => {
                    const cityElem = document.createElement('p');
                    cityElem.setAttribute('class', 'select-city__list_item');
                    cityElem.addEventListener('click', () => {
                        this.onSetRegion(cityId);
                    });
                    cityElem.innerText = this.loctionList[cityId];
                    citysWrapper.append(cityElem);
                });

            oneliterWrapper.append(citysWrapper);
            litersWrapper.append(oneliterWrapper);
        })


        const regionKeys = Object.keys(this.loctionList);
        Array.from(exapmleRegions.querySelectorAll('span')).forEach((item, index) => {
            item.innerText = this.loctionList[regionKeys[index]];
            item.addEventListener('click', () => {
                this.onSetRegion(regionKeys[index]);
            });
        });

    }

    renderCountryTabs() {
        const countryWrap = this.getEntity(this.selectRegion, 'sotbit-regions-tabs');

        if (Array.from(countryWrap.childNodes).filter(i => i instanceof HTMLLIElement).length > 0) {
            return;
        }

        const activClass = 'active';

        Object.keys(this.countryList).forEach(i => {

            const element = document.createElement('li');
            if (this.activCountry === i) {
                element.setAttribute('class', `select-city__tab ${activClass}`);
            } else {
                element.setAttribute('class', 'select-city__tab');
            }

            element.innerText = this.countryList[i];

            element.addEventListener('click', () => {
                if (element.classList.contains(activClass)) {
                    return;
                }

                const newCountryWrap = this.getEntity(this.selectRegion, 'sotbit-regions-tabs');
                    Array.from(newCountryWrap.childNodes).forEach(j =>  {
                        if (j instanceof HTMLLIElement) {
                            j.classList.remove(activClass)
                        }
                    });

                element.classList.add(activClass);
                this.onShowALLRegions(i);

            })

            countryWrap.append(element);

            countryWrap.childNodes.forEach(i => {
                if (!(i instanceof HTMLLIElement)) {
                    return;
                }
            })

        });
    }

    removeRegionGetParams(query) {
        if (query.has('redirectRegionId')) {
            const url = new URL(window.location.href, window.location.href);
            url.searchParams.delete('redirectRegionId');
            window.history.replaceState(null, '', url)
        }
    }
}
;