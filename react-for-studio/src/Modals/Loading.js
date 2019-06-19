import React from 'react';
import LoadingLayer from '../Loading';
import ModalWrapper from './wrapper/ModalWrapper';

const Loading = () => {
    return (
        <ModalWrapper>
            <div className="modal-content">
                <LoadingLayer />
            </div>
        </ModalWrapper>
    );
};

export default Loading;
