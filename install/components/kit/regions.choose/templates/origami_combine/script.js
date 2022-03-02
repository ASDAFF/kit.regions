class RegionsChoose {

    constructor() {

        this.getRegion();

        this.rootId = 'regions_choose_component';
        this.rootDropDownId = 'regions_choose_component_dropdown';
        this.selectRegionID = 'regon_choose_select-city__modal';
        this.selectRegionOverlayID = 'regon_choose_modal__overlay';
    
        this.questionRegionId = null;
        this.loctionList = null;

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
        const selectRegionBtn = this.getEntity(this.selectRegion, 'select-city__modal__submit__btn');
        const selectConfirmRegion = this.getEntity(this.selectRegion, 'select-city__automatic');

        yesBtn.addEventListener('click', () => this.onSetRegion(this.questionRegionId));
        notBnt.addEventListener('click', () => this.onShowALLRegions());
        cityName.addEventListener('click', () => this.onShowALLRegions());
        selectRegionClose.addEventListener('click', () => this.onSelectRegionShow(false));
        searchLine.addEventListener('input', e => this.onInputSearch(e));
        selectConfirmRegion.addEventListener('click', () => this.getRegion(true));


        selectRegionBtn.addEventListener('click', () => {
            const selectedRegion = Object.keys(this.loctionList)
                .filter(i => searchLine.value === this.loctionList[i]);

            if (selectedRegion.length === 0) {
                return;
            }

            this.onSetRegion(selectedRegion[0]);
            this.onShowALLRegions(false);
        })

    }

    onSetRegion(regionId) {
        BX.ajax.runAction('kit:regions.ChooseComponentController.setRegion', {
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

        if (this.loctionList !== null) {
            return this.SHOW_SELECT_REGIONS(this.loctionList);
        }

        BX.ajax.runAction('kit:regions.ChooseComponentController.showLocations', {})
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

        BX.ajax.runAction('kit:regions.ChooseComponentController.getRegion', {
            data: params,
        })
        .then(
            (res) => {
                res.data.actions.forEach(i => this[i](res.data));
                if (deleteCookies) {
                    this.dropDownShow(false);
                    this.onSelectRegionShow(false);
                    this.onSetRegion(res.data.currentRegionId);
                }
            },
            (err) => console.log(err),
        )

    }

    onSelectRegionShow(action) {
        if (action) {
            this.selectRegion.setAttribute('style', 'display: block;');
            this.selectRegionOverlay.setAttribute('style', 'display: block;');
        } else {
            this.selectRegionOverlay.setAttribute('style', 'display: none;');
            this.selectRegion.setAttribute('style', 'display: none;');
        }
    }

    SHOW_REGION_NAME ({currentRegionName, currentRegionId}) {
        const elment = this.getEntity(this.root, 'select-city__block__text-city');
        elment.innerText = currentRegionName;
        this.currentRegionId = currentRegionId;
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

    SHOW_SELECT_REGIONS ({allRegions}) {

        this.onSelectRegionShow(true);

        if (this.loctionList !== null) {
            return;
        }

        this.loctionList = allRegions;
        const exapmleRegions = this.getEntity(this.selectRegion, 'select-city__input__example');
        const regionKeys = Object.keys(this.loctionList);
        Array.from(exapmleRegions.querySelectorAll('span')).forEach((item, index) => {
            item.innerText = this.loctionList[regionKeys[index]];
            item.addEventListener('click', () => {
                this.onSetRegion(regionKeys[index]);
                this.onShowALLRegions(false);
            })
        })
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

        const elementClass = 'regions_vars';
        this.getEntity(this.selectRegion, elementClass, true).forEach(i => i.remove());
        const text = e.currentTarget.value;
        const localRootElement = this.getEntity(this.selectRegion, 'select-city__modal__submit__vars');

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
                this.getEntity(this.selectRegion, 'select-city__modal__submit__btn').removeAttribute('disabled')
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