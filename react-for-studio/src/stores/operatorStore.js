import { observable, computed, action } from 'mobx';
import _ from 'lodash';
import Request from '../library/utils/Request';

export default class operatorStore {
    @observable operators = null;
    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
    }

    @computed get getOperators() {
        return this.operators;
    }

    @action
    loadOperators() {
        this.request
            .get(`/console/apps/op/list`)
            .then(res => res.data.data)
            .then(data => {
                this.setOperators(data.operator);
            });
    }
    @action
    setOperators(op) {
        this.operators = op;
    }
    @action
    unsetOperators() {
        this.operators = null;
    }
}
