import { observable, computed, action } from 'mobx';
import Request from '../library/utils/Request';
export default class shareStore {
    @observable step = 1;
    @observable partnerId = '';
    @observable expireDate = '';
    @observable shareUrl = '';
    opId = null;
    appId = null;
    bizId = null;

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
    }

    @computed get getStep() {
        return this.step;
    }
    @computed get getPartnerId() {
        return this.partnerId;
    }
    @computed get getExpireDate() {
        return this.expireDate;
    }
    @computed get getShareUrl() {
        return this.shareUrl;
    }

    @action
    setBaseId(app_id, biz_id) {
        this.appId = app_id;
        this.bizId = biz_id;
    }

    @action
    setShareOp(op_id) {
        this.step = 2;
        this.opId = op_id;
    }
    @action
    setPartnerId(id) {
        this.partnerId = id;
    }
    @action
    setExpire(d) {
        this.expireDate = d;
    }
    @action
    callSharedUrl() {
        if (this.partnerId === '') {
            this.step = 3;
            return false;
        }
        if (this.expireDate === '') {
            this.step = 4;
            return false;
        }
        this.request
            .post('/console/apps/bunit/makeurl', {
                app_id: this.appId,
                biz_id: this.bizId,
                op_id: this.opId,
                partner_id: this.partnerId,
                expireDate: this.expireDate,
            })
            .then(res => res.data.data)
            .then(data => {
                this.setShareUrl(data.uri);
            });
    }
    @action
    setShareUrl(url) {
        this.shareUrl = `${location.origin}${location.pathname}#${url}`;
    }

    @action
    prev() {
        this.step = this.step <= 1 ? 1 : this.step - 1;
    }

    @action
    next() {
        this.step = this.step >= 6 ? 6 : this.step + 1;
    }

    @action
    resetStore() {
        this.step = 1;
        this.partnerId = '';
        this.expireDate = '';
        this.shareUrl = '';
        this.opId = null;
    }
    @action
    closeStore() {
        this.resetStore();
        this.appId = null;
        this.bizId = null;
        this.rootStore.modalStore.hideModal();
    }
}
