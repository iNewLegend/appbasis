/**
 * @file: api/authorization/model.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description: 
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export enum API_Model_Authorization_States {
    PREPARE,
    NONE,
    VERIFY,
    UNAUTHORIZED,
    AUTHORIZED,
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export interface API_Authorization_Register_Send {
    email: string;
    password: string;
    repassword: string;
    captcha: string;
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export interface API_Model_Authorization_Recv {
    code: string,
    subcode: string;
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export interface API_Model_Authorization_Send {
    email: string;
    password: string;
    captcha: string;
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------