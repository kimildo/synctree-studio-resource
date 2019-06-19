import { observable, action } from 'mobx';

export default class ModalStore {
    @observable isOpen = false;
    @observable params = null;

    constructor(rootStore) {
        this.rootStore = rootStore;
    }

    // params : {type, data}
    @action
    showModal(params) {
        this.isOpen = true;
        this.params = params;
    }
    @action
    hideModal() {
        this.isOpen = false;
        this.params = null;
    }
}
