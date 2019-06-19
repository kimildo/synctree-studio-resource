import { observable, computed, action } from 'mobx';
import _ from 'lodash';
// import Util from '../library/utils/Util';
import Request from '../library/utils/Request';
/* global global_data $ */

export default class bizOpsStore {
    @observable ops = null;
    loading = false;

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
    }

    @computed get getBizOps() {
        return this.ops;
    }

    @action
    setBizOps(ops) {
        this.ops = ops;
    }

    @action
    loadBizOps(app_id = null) {
        this.resetOps();
        app_id = app_id || this.rootStore.userInfoStore.getSelectedApp.app_id;
        if (this.loading) {
            return false;
        }
        this.loading = true;
        this.request
            .get(`/console/apps/bunit/${app_id}`)
            .then(res => res.data.data)
            .then(data => {
                this.setBizOps(data.biz_ops);
                this.loading = false;
            });
    }

    @action
    addBizOps(params) {
        this.request.post('/console/apps/bunit/add', params).then(res => {
            this.rootStore.modalStore.hideModal();
            this.loadBizOps();
        });
    }

    // 현재 Backend 구현 안됨
    @action
    deleteBizOps(params) {
        this.request.post(`/console/apps/bunit/remove`, params).then(res => {
            if (res.data.result === 'success') {
                this.loadBizOps();
            }
        });
    }

    @action
    resetOps() {
        this.ops = null;
    }
}
