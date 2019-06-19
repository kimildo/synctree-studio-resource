import { observable, computed, action } from 'mobx';
import _ from 'lodash';
import Request from '../../library/utils/Request';

/* global global_data */

export default class DocsStore {
    @observable data = null;
    @observable language = 'PHP';
    @observable loadError = false;
    langList = { PHP: 6, Node: 5 };
    request = Request;

    constructor() {
        this.app_id = global_data.app_id;
        this.biz_id = global_data.biz_id;
        if (!!document.querySelector('#document')) {
            this.loadData();
        }
    }

    @computed get getData() {
        return this.data;
    }

    @action
    loadData() {
        this.request
            .post(`/console/apps/bunit/buildCallback/getData`, {
                app_id: this.app_id,
                biz_id: this.biz_id,
            })
            .then(res => res.data)
            .then(data => {
                this.setData(data);
            })
            .catch(() => {
                this.errorLoading();
                // console.error('loadData Error : ', this.loadError);
            });
    }
    @action
    errorLoading() {
        this.loadError = true;
    }

    @action
    changeLang(l) {
        this.language = l;
    }

    @action
    setData(d) {
        this.data = d;
    }

    @action
    resetData() {
        this.data = null;
    }
}
