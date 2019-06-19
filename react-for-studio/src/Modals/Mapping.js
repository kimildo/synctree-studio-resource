import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Loading from './Loading';
import InputParams from './include/Mapping/InputParams';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Util from '../library/utils/Util';
/* global global_data */

@inject('mappingStore')
@observer
class Mapping extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
    }
    componentDidMount() {
        const { opIdxs } = this.props.data;

        if (this.props.mappingStore.mappingGetData === null) {
            this.props.mappingStore.getMappingData(opIdxs);
        }
        this.closeModal = this.closeModal.bind(this);
    }
    closeModal() {
        this.props.mappingStore.unsetMapping();
    }

    getInputFields() {
        const { mappingSetData } = this.props.mappingStore;
        return mappingSetData.map((row, i) => (
            <InputParams data={row} index={i} key={`getInputFields_${i}`} />
        ));
    }

    save(e) {
        e.preventDefault();
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.mappingStore.saveMappingData(this.props.data.bindingSeq);
        }
    }
    getViewLayer() {
        const { dictionary } = global_data;

        const inputFields = this.getInputFields();
        let returnData = <Loading />;
        if (this.props.mappingStore.mappingGetData !== null) {
            returnData = (
                <ModalWrapper>
                    <form
                        className="modal-content"
                        ref={this.formEl}
                        onSubmit={this.save.bind(this)}>
                        <ModalHeader closeModal={this.closeModal}>
                            <i className="fa fa-lg fa-fw fa-exchange " />{' '}
                            <span>Mapping</span>
                        </ModalHeader>
                        <div className="modal-body">
                            <div className="widget-body no-padding">
                                <div className="smart-form">
                                    <header>
                                        <strong>Set form value</strong>
                                    </header>
                                    <fieldset className="fieldset-input">
                                        {inputFields}
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <ModalFooter closeModal={this.closeModal}>
                            <button className="btn btn-success" type="submit">
                                <i className="fa fa-save" />{' '}
                                {dictionary.button.save}
                            </button>
                        </ModalFooter>
                    </form>
                </ModalWrapper>
            );
        }
        return returnData;
    }

    render() {
        const view = this.getViewLayer();
        return <>{view}</>;
    }
}

export default Mapping;
