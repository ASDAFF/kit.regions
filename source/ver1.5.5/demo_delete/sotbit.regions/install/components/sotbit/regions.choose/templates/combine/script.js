window.SotbitRegions = function (arParams) {

    let wrap = document.querySelector(".select-city__dropdown-wrap"),
        modal = document.querySelector(".select-city__modal"),
        overlay = document.querySelector(".modal__overlay"),
        yes = document.querySelector(".select-city__dropdown__choose__yes"),
        no = document.querySelector(".select-city__dropdown__choose__no"),
        textCity = document.querySelector(".select-city__block__text-city__js"),
        params = JSON.parse(arParams.arParams);
    let response;

    yes.addEventListener('click', function (e) {
            //createMainLoader($('body'));
            e.preventDefault();
            wrap.style.display = 'none';

            if (params.FROM_LOCATION === 'Y')
            {
                var idLocation = yes.dataset.id;
                var idRegion = yes.dataset.regionId;
                var codeRegion = yes.dataset.code;

                setCookie('sotbit_regions_location_id',idLocation,{'domain': '.' + arParams.rootDomain});
                setCookie('sotbit_regions_city_choosed','Y',{'domain': '.' + arParams.rootDomain});
                setCookie('sotbit_regions_id', idRegion,{'domain': '.' + arParams.rootDomain});
                if(arParams.singleDomain != 'Y' && codeRegion)
                    document.location.href = codeRegion;

                /*let idLocation = yes.dataset.id;
                setCookie('sotbit_regions_location_id', idLocation, {'domain': arParams.rootDomain});
                setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});

                let xhr = new XMLHttpRequest();
                let body = 'id=' + idLocation + '&action=getDomainByLocation';

                xhr.open("POST", arParams.componentFolder + '/ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function () {
                    //removeMainLoader();
                };

                xhr.onreadystatechange = function () {
                    if (this.readyState === 4)
                    {
                        let answer = JSON.parse(this.responseText);

                        if (answer.ID) {
                            setCookie('sotbit_regions_id', answer.ID, {'domain': arParams.rootDomain});

                            if (arParams.singleDomain === 'Y') {
                                //location.reload();
                            } else {
                                document.location.href = answer.CODE;
                            }
                        }
                    }

                };

                xhr.send(body);*/

            } else {

                setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                setCookie('sotbit_regions_id', yes.dataset.id, {'domain': '.' + arParams.rootDomain});

                if (arParams.singleDomain !== 'Y') {
                    let url = '';
                    for (let i = 0; i < arParams.list.length; ++i) {
                        if (arParams.list[i]['ID'] == yes.dataset.id) {
                            url = arParams.list[i]['URL'];
                        }
                    }
                    if (url.length > 0) {
                        document.location.href = url;
                    }
                } else {
                    //location.reload();
                }

                //removeMainLoader();
            }
        }
    );

    function openModal() {
        //createMainLoader($('body'));

        let regionsType = arParams.locationType === 'location' ? 'type=location' : 'type=regions';
        if (arParams.list.length === 0) {
            isList = false;
            regionsType += '&getBase=Y';
        }

        if (response === undefined) {
            let xhr = new XMLHttpRequest();

            xhr.open("POST", arParams.templateFolder + '/ajax.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                //removeMainLoader();
            };

            xhr.onreadystatechange = function () {
                if (this.readyState === 4) {

                    let answer = JSON.parse(this.responseText);
                    response = answer;

                    createModalData(answer, regionsType);
                }
            };

            xhr.send(regionsType);

        } else {
            createModalData(response, regionsType);
            //removeMainLoader();
        }
    }

    function createModalData(answer, regionsType) {
        let isList = true;

        if (arParams.list.length === 0) {
            isList = false;
        }

        modal.innerHTML = answer['TEMPLATE'];
        modal.style.display = 'block';
        overlay.style.display = 'block';
        wrap.style.display = 'none';

        let autoButton = document.querySelector(".select-city__automatic a");

        autoButton.addEventListener('click', determineAutomatic);

        let cityInMessage = document.getElementsByClassName("select-city__under_input");

        if (cityInMessage.length) {

            if (cityInMessage[0].children.length) {

                for (let i = 0; i < cityInMessage[0].children.length; ++i) {

                    cityInMessage[0].children[i].addEventListener('click', function () {
                        let idRegion = this.dataset.regionId;
                        let codeRegion = this.dataset.regionCode;
                        let idLocation = this.dataset.locationId;

                        if (params.FROM_LOCATION === 'Y') {

                            setCookie('sotbit_regions_location_id', idLocation, {'domain': arParams.rootDomain});
                            setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                            setCookie('sotbit_regions_id', idRegion, {'domain': arParams.rootDomain});

                            if (arParams.singleDomain === 'Y') {
                                location.reload();
                            } else {
                                document.location.href = codeRegion;
                            }
                        } else {

                            setCookie('sotbit_regions_id', idRegion, {'domain': arParams.rootDomain});
                            setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                            if (arParams.singleDomain === 'Y') {
                                location.reload();
                            } else {
                                document.location.href = codeRegion;
                            }
                        }

                    });
                }

            }

        }

        //$(document).on('input', '#region-input', function () {
        BX.bind(BX('region-input'), 'input', function(){
            let regionInput = document.querySelector("#region-input"),
                responseElementsContainer = document.querySelector(".select-city__response"),
                inputText = regionInput.value.toLowerCase(),
                submitButton = document.querySelector(".btn.select-city-button.regions_choose"),
                responseAsObject = isList ? arParams.list : answer['LOCATION'];

            if (inputText.length > 1) {

                let arrayOfResponseNames = [];

                for (let key in responseAsObject) {
                    let cityName = responseAsObject[key].NAME.toLowerCase();

                    if (cityName.indexOf(inputText) === 0) {
                        arrayOfResponseNames[arrayOfResponseNames.length] = responseAsObject[key];
                    }
                }

                createResponseElements(arrayOfResponseNames);

                let responseElemChildren = responseElementsContainer.querySelectorAll("div");
                let dataId,
                    domain;

                for (let i = 0; i < responseElemChildren.length; i++) {
                    responseElemChildren[i].addEventListener("click", function (event) {
                        let element = event.target;
                        dataId = element.dataset.id;

                        for (let key in responseAsObject) {
                            if (responseAsObject[key].ID == dataId) {
                                regionInput.value = responseAsObject[key].NAME;
                                domain = responseAsObject[key].CODE;
                                destroyChildren(responseElementsContainer);
                                break;
                            }
                        }
                        setSubmitButtonEvent(dataId, arParams);
                    });
                }

                for (let key in responseAsObject) {
                    if (inputText.toLowerCase() === responseAsObject[key].NAME.toLowerCase()) {
                        dataId = responseAsObject[key].ID;
                        domain = responseAsObject[key].CODE;
                    }
                }

                if (dataId !== undefined) {
                    setSubmitButtonEvent(dataId, arParams);
                } else {
                    submitButton.disabled = true;
                }

            } else {
                destroyChildren(responseElementsContainer);
                resetRegionInputStyles(regionInput);
                submitButton.disabled = true;
            }

            function setSubmitButtonEvent(ID, arParams) {
                submitButton.disabled = false;

                submitButton.addEventListener('click', function () {

                    //createBtnLoader(submitButton);

                    setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});

                    if (params.FROM_LOCATION === 'Y')
                    {
                        setCookie('sotbit_regions_location_id', ID, {'domain': '.' + arParams.rootDomain});

                        let idLocation = ID;
                        let xhr = new XMLHttpRequest();
                        let body = 'id=' + idLocation;

                        if (arParams.singleDomain === 'Y')
                            body += '&action=getDomainByLocation';
                        else body += '&action=getMultiDomainByLocation';

                        xhr.open("POST", arParams.componentFolder + '/ajax.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                        xhr.onreadystatechange = function () {
                            if (this.readyState !== 4) return;
                            let answer = JSON.parse(this.responseText);
                            if (answer.ID)
                            {
                                setCookie('sotbit_regions_id', answer.ID, {'domain': '.' + arParams.rootDomain});

                                if (arParams.singleDomain === 'Y') {
                                    location.reload();
                                } else {
                                    document.location.href = answer.CODE;
                                }
                            }

                            //removeBtnLoader(submitButton);
                            closeModal();
                        };

                        xhr.send(body);

                    } else {

                        setCookie('sotbit_regions_id', ID, {'domain': '.' + arParams.rootDomain});

                        if (arParams.singleDomain !== 'Y') {
                            let url = '';
                            for (let i = 0; i < responseAsObject.length; ++i) {
                                if (responseAsObject[i]['ID'] === ID) {
                                    url = arParams.list[i]['URL'] ? arParams.list[i]['URL'] : arParams.list[i]['CODE'];
                                }
                            }

                            if (url.length > 0) {
                                document.location.href = url;
                            }

                        } else {
                            location.reload();
                        }

                        //removeBtnLoader(submitButton);
                        closeModal();
                    }
                })
            }

            function createResponseElements(responseArray) {
                destroyChildren(responseElementsContainer);

                for (let i = 0; i < responseArray.length; i++) {
                    let newResponseElement = document.createElement("div");

                    newResponseElement.textContent = responseArray[i].NAME;
                    newResponseElement.setAttribute("data-id", responseArray[i].ID);

                    responseElementsContainer.appendChild(newResponseElement);
                    setRegionInputStyles(regionInput);
                }
            }

        });

        document
            .querySelector(".select-city__close")
            .addEventListener('click', closeModal);

        overlay.addEventListener('click', closeModal);
    }

    function determineAutomatic(e) {

        e.preventDefault();

        //createBtnLoader(document.querySelector(".select-city__automatic > a"));

        let xhr = new XMLHttpRequest();
        let body = 'action=getAutoRegion';
        xhr.open("POST", arParams.templateFolder + '/ajax.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (this.readyState !== 4) return;

            //removeBtnLoader(document.querySelector(".select-city__automatic > a"));

            let answer = JSON.parse(this.responseText);

            if (params.FROM_LOCATION === 'Y') {

                setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                setCookie('sotbit_regions_id', answer.ID, {'domain': arParams.rootDomain});
                setCookie('sotbit_regions_location_id', answer.LOCATION.ID, {'domain': arParams.rootDomain});

                let regionInput = document.querySelector("#region-input");

                regionInput.value = answer.LOCATION.SALE_LOCATION_LOCATION_NAME_NAME;

                if (answer.ID) {
                    if (arParams.singleDomain === 'Y') {
                        location.reload();
                    } else {
                        document.location.href = answer.CODE;
                    }
                }
            } else {

                setCookie('sotbit_regions_city_choosed', 'Y', {'domain': '.' + arParams.rootDomain});
                setCookie('sotbit_regions_id', answer.ID, {'domain': arParams.rootDomain});

                let regionInput = document.querySelector("#region-input");

                regionInput.value = answer.NAME;

                if (answer.ID) {
                    if (arParams.singleDomain === 'Y') {
                        location.reload();
                    } else {
                        document.location.href = answer.CODE;
                    }
                }
            }
        };

        xhr.send(body);

    }

    no.addEventListener('click', function () {
        openModal();
    });

    textCity.addEventListener('click', function () {
        openModal();
    });

    function destroyChildren(element) {
        element.innerHTML = '';
    }

    function setRegionInputStyles(regionInputElement) {
        regionInputElement.style.position = "absolute";
        regionInputElement.style.boxShadow = "0px 6px 12px rgba(0, 0, 0, 0.15)";
        regionInputElement.style.border = "none";
        regionInputElement.style.borderBottom = "1px solid #ededed";

        let responseWrapper = document.querySelector(".select-city__response_wrapper");
        responseWrapper.style.height = regionInputElement.offsetHeight + "px";
        responseWrapper.style.width = regionInputElement.offsetWidth + "px";
    }

    function resetRegionInputStyles(regionInputElement) {
        regionInputElement.style.position = "initial";
        regionInputElement.style.boxShadow = "none";
        regionInputElement.style.border = "1px solid #ededed;";
    }

    function setCookie(name, value, options) {
        options = options || {};

        let expires = options.expires;

        if (typeof expires == "number" && expires) {
            let d = new Date();
            d.setTime(d.getTime() + expires * 1000);
            expires = options.expires = d;
        }
        if (expires && expires.toUTCString) {
            options.expires = expires.toUTCString();
        }
        options.path = '/';
        value = encodeURIComponent(value);

        let updatedCookie = name + "=" + value;

        for (let propName in options) {
            updatedCookie += "; " + propName;
            let propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    }

    function closeModal() {
        let submitButton = document.querySelector(".btn.select-city-button.regions_choose");
        let regionInput = document.querySelector("#region-input");
        let responseElementsContainer = document.querySelector(".select-city__response");

        submitButton.disabled = true;
        regionInput.value = '';
        resetRegionInputStyles(regionInput);
        destroyChildren(responseElementsContainer);

        modal.style.display = 'none';
        overlay.style.display = 'none';
    }
};