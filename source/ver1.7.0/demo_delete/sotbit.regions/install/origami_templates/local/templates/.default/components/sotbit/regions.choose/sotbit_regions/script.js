;
class RegionsChoose {

    constructor() {

        this.origamiMenuCity = null;
        this.questionRegionId = null;
        this.loctionList = null;

        this.rootId = 'regions_choose_component';
        this.rootDropDownId = 'regions_choose_component_dropdown';
        this.selectRegionID = 'regon_choose_select-city__modal';
        this.selectRegionOverlayID = 'regon_choose_modal__overlay';

        this.getRegion();

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
        const cityName = this.getEntity(this.root, 'open_region');
        const selectRegionClose = this.getEntity(this.selectRegion, 'select-city__close');
        const searchLine = this.getEntity(this.selectRegion, 'select-city__modal__submit__input');

        this.addSelectBatnEvent(this.selectRegion, searchLine);

        window.addEventListener('load', () => {
            this.origamiMenuCity = document.getElementById('menu-city');
            if (!this.origamiMenuCity) {
                return;
            }
            this.origamiMenuCity.addEventListener('click', e => {
                if (e.target.id === 'menu-city') {
                    this.onShowALLRegions();
                }
            })
        });
        yesBtn.addEventListener('click', () => this.onSetRegion(this.questionRegionId));
        notBnt.addEventListener('click', () => {
            this.onShowALLRegions();
            this.dropDownShow(false);
        });
        cityName.addEventListener('click', () => this.onShowALLRegions());
        selectRegionClose.addEventListener('click', () => this.onSelectRegionShow(false));
        searchLine.addEventListener('input', e => this.onInputSearch(e))

    }

    addSelectBatnEvent(root, searchLine) {
        const selectRegionBtn = this.getEntity(root, 'select-city__modal__submit__btn');
        selectRegionBtn.addEventListener('click', () => {
            const selectedRegion = Object.keys(this.loctionList)
                .filter(i => searchLine.value.toLowerCase() === this.loctionList[i].toLowerCase());

            if (selectedRegion.length === 0) {
                this.getEntity(root, 'select-city__modal__submit__block-wrap__input_wrap_error')
                    .setAttribute('style', '');
                return;
            }

            this.onSetRegion(selectedRegion[0]);
            this.onSelectRegionShow(false);
        })
    }

    onSetRegion(regionId) {
        BX.ajax.runAction('sotbit:regions.ChooseComponentController.setRegion', {
            data: {regionId: regionId},
        }).then(
            (res) => res.data.actions.forEach(i => this[i](res.data)),
            (err) => console.log(err),
        )
    }

    dropDownShow(action) {
        if (action) {
            this.rootDropDown.setAttribute('style', 'display: block;');
        } else {
            this.rootDropDown.setAttribute('style', 'display: none;');
        }
    }

    onShowALLRegions() {

        BX.ajax.runAction('sotbit:regions.ChooseComponentController.showLocations', {})
            .then(
                (res) => res.data.actions.forEach(i => this[i](res.data)),
                (err) => console.log(err),
            )
    }

    getRegion() {

        const query = new URLSearchParams(window.location.search);

        this.removeRegionGetParams(query);

         BX.ajax.runAction('sotbit:regions.ChooseComponentController.getRegion', {
            data: {redirectRegionId: query.get('redirectRegionId')},
        })
        .then(
            (res) => res.data.actions.forEach(i => this[i](res.data)),
            (err) => console.log(err),
        )

    }

    onSelectRegionShow(action) {

        const origamiMenu = document.getElementById('menu-header-three');
        if (origamiMenu && origamiMenu.classList.contains('show') && this.origamiMenuCity) {

            let selectRegionCopy = null;

            if (action) {
                if (!this.origamiMenuCity.querySelector(`#${this.selectRegionID}`)) {
                    selectRegionCopy = this.selectRegion.cloneNode(true);
                    selectRegionCopy.classList.forEach(i => {
                        selectRegionCopy.classList.remove(i);
                    });
                    selectRegionCopy.classList.add('select-city__modal_for_menu');
                    const searchLine = this.getEntity(selectRegionCopy, 'select-city__modal__submit__input');
                    this.addSelectBatnEvent(selectRegionCopy, searchLine);
                } else {
                    selectRegionCopy = this.origamiMenuCity.querySelector(`#${this.selectRegionID}`);
                }

                selectRegionCopy.setAttribute('style', 'display: block;');
                const selectRegionClose = this.getEntity(selectRegionCopy, 'select-city__close');
                selectRegionClose.addEventListener('click', () => this.onSelectRegionShow(false));
                this.origamiMenuCity.append(selectRegionCopy);
            } else {
                const selectRegionCopy = this.origamiMenuCity.querySelector(`#${this.selectRegionID}`)
                selectRegionCopy.setAttribute('style', 'display: none;');
            }

            return selectRegionCopy;
        }


        if (action) {
            this.selectRegion.setAttribute('style', 'display: block;');
            this.selectRegionOverlay.setAttribute('style', 'display: block;');
        } else {
            this.selectRegionOverlay.setAttribute('style', 'display: none;');
            this.selectRegion.setAttribute('style', 'display: none;');
        }

        this.getEntity(this.selectRegion, 'select-city__modal__submit__block-wrap__input_wrap_error')
            .setAttribute('style', 'display: none;');

        return this.selectRegion;
    }

    SHOW_REGION_NAME ({currentRegionName}) {
        const elment = this.getEntity(this.root, 'open_region');
        elment.innerText = currentRegionName;
        this.getEntity(this.selectRegion, 'select-city__js').innerText = currentRegionName;
    }

    SHOW_QUESTION ({currentRegionName, currentRegionId}) {
        this.questionRegionId = currentRegionId;
        this.dropDownShow(true);
        const element = this.getEntity(this.rootDropDown, 'select-city__dropdown__title');
        element.innerText = element.textContent.replace('###', currentRegionName).trim();
    }

    CONFIRM_DOMAIN ({currentRegionName}) {
        this.dropDownShow(false);
        const elemnt = this.getEntity(this.root, 'open_region');
        elemnt.innerText = currentRegionName;
        this.getEntity(this.selectRegion, 'select-city__js').innerText = currentRegionName;
    }

    SHOW_SELECT_REGIONS ({allRegions}) {

        const selectRegion = this.onSelectRegionShow(true);


        const count = this.getEntity(selectRegion, 'select-city__modal__list').childNodes.length;

        if (this.loctionList !== null && count > 3) {
            return;
        }

        const localRootElement = this.getEntity(selectRegion, 'select-city__modal__list');

        this.loctionList = allRegions;

        let counter = 0;

        for (let i in allRegions) {
            const element = document.createElement('p');
            element.setAttribute('data-entity', 'select-city__modal__list__item');
            element.setAttribute('class', 'select-city__modal__list__item');
            element.innerText = allRegions[i];
            element.addEventListener('click', () => {
                this.onSetRegion(i);
                this.onSelectRegionShow(false);
            });
            localRootElement.append(element);
            counter++;
            if (counter > 14) {
                return;
            }
        }
    }

    REDIRECT_TO_SUBDOMAIN ({currentRegionCode, currentRegionId}) {
        const hostName = window.location.hostname;
        const protocol = window.location.protocol;
        const newUrl = window.location.href.replace(hostName, currentRegionCode);
        const url = new URL(newUrl, `${protocol}${currentRegionCode}`);
        url.searchParams.set('redirectRegionId', currentRegionId);
        window.location.href = url.toString();
    }

    CONFIRM_CITY ({}) {
    }

    onInputSearch(e) {

        const elementClass = 'regions_vars';
        this.getEntity(this.selectRegion, elementClass, true).forEach(i => i.remove());
        const text = e.currentTarget.value;
        const localRootElement = this.getEntity(this.selectRegion, 'select-city__modal__submit__vars');
        this.getEntity(this.selectRegion, 'select-city__modal__submit__block-wrap__input_wrap_error')
            .setAttribute('style', 'display: none;');

        if (text.length  < 2) {
            localRootElement.setAttribute('style', 'display: none;');
            return;
        }

        const currentTarget = e.currentTarget;

        const match = Object.keys(this.loctionList).filter(i => {
            return  new RegExp(text, 'i').test(this.loctionList[i])
        });

        if (match.length > 0) {
            localRootElement.setAttribute('style', 'display: block;');
        } else {
            localRootElement.setAttribute('style', 'display: none;');
        }

        match.forEach(i => {
            const element = document.createElement('div');
            element.setAttribute('data-entity', elementClass);
            element.setAttribute('class', elementClass);
            element.setAttribute('tabindex', 0);
            element.innerText = this.loctionList[i];
            element.addEventListener('click', () => {
                currentTarget.value = this.loctionList[i];
                this.getEntity(this.selectRegion, elementClass, true).forEach(i => i.remove());
                localRootElement.setAttribute('style', 'display: none;');
            });
            localRootElement.append(element);
        })
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