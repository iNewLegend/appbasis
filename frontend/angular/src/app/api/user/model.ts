/**
 * @file: api/user/model.ts
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @description: 
 * @todo:
 */
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export class API_Model_User_Index_Recv {
    id: String;
    isactive: Boolean;
    email: String;
    firstname: String;
    lastname: String
    created_at: String;
    updated_at: String;
    roles: Array<Number>;
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------

export enum API_Model_User_Roles {
    NONE,
    OWNER,
    ADMIN,
    EDITOR
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------