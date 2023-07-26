create function user_commissions() returns trigger
    language plpgsql
as
$$
DECLARE
    tree json;
    usuarios json;
    comTotal numeric;
    comisionLevel numeric[];
    comisionLiquida numeric;
    comisionRetenida numeric;
    level int;

    absDepth numeric;

BEGIN
      RAISE NOTICE '%%%', NEW ;
    IF NEW.type = 'Reinvestment' THEN
        INSERT INTO histories (user_id, deposit_id, amount, type, description, actual_amount, created_at, pay_id) VALUES
        (NEW.user_id, NEW.id,-(new.amount), 'Deposit', 'Confirmed reinvestment deposit', -(new.amount), now(), new.pay_id);
    ELSE
        INSERT INTO histories (user_id, deposit_id, amount, type, description, actual_amount, created_at, pay_id) VALUES
        (NEW.user_id, NEW.id,new.amount, 'Add_funds', 'Add funds', new.amount, now(), new.pay_id);
        INSERT INTO histories (user_id, deposit_id, amount, type, description, actual_amount, created_at, pay_id) VALUES
        (NEW.user_id, NEW.id,-(new.amount), 'Deposit', 'Confirmed deposit', -(new.amount), now(), new.pay_id);
    END IF;
comisionLevel:=(select commission_level from parameters);
level:= (sum(array_length(comisionLevel,1))::numeric);
      
  
    usuarios:=(select json_agg(row) from(WITH RECURSIVE parents AS (
            SELECT user_id, income, 0 as depth, username,0.00 as amount
            FROM referal_stats INNER JOIN users
            ON referal_stats.user_id = users.id
            WHERE user_id = NEW.user_id
           UNION
            SELECT op.user_id, op.income, depth - 1, u.username,(case when (
                (select sum(amount) from deposits where user_id=op.user_id and status='on')::numeric)is null then 0
                when ((select sum(amount) from deposits where user_id=op.user_id and status='on')::numeric)is not null
                then (select sum(amount) from deposits where user_id=op.user_id and status='on')::numeric end)
                as amount
            FROM referal_stats op INNER JOIN users u
            ON op.user_id = u.id
            JOIN parents c ON op.user_id = c.income
           )
           SELECT *
           FROM parents where depth >= -(level) and depth!=0 AND amount!=0)row);
    for tree in select * from json_array_elements(usuarios::json)
    LOOP

        if((tree->>'amount')::numeric>0)then
            absDepth := (select abs((tree->>'depth')::numeric))::numeric;
           
            comTotal := (comisionLevel[absDepth]/100)* NEW.amount;
            
       
            
            if(NEW.amount>(tree->>'amount')::numeric)then

                comisionLiquida:=((tree->>'amount')::numeric)*(comisionLevel[absDepth]/100);

                

                --insert
                insert into histories (user_id, deposit_id, pay_id,amount, type, description, actual_amount, created_at, updated_at)
                values ((tree->>'user_id')::bigint, NEW.id, NEW.pay_id,comisionLiquida, 'Enable_commission', ' Commission', comisionLiquida, now(), now());
                comisionRetenida:=comTotal-comisionLiquida;
                --insert
                insert into histories (user_id, deposit_id, pay_id, amount, type, description, actual_amount, created_at, updated_at)
                values ((tree->>'user_id')::bigint, NEW.id, NEW.pay_id,comisionRetenida, 'Pending_commission', 'Commission', comisionRetenida, now(), now());
            else
                --insert
                insert into histories (user_id, deposit_id, pay_id, amount, type, description, actual_amount, created_at, updated_at)
                values ((tree->>'user_id')::bigint, NEW.id, NEW.pay_id,comTotal, 'Enable_commission', 'Commission', comTotal, now(), now());
            end if;
        end if;
    END LOOP;
    RETURN NULL;
END;
$$;

alter function user_commissions() owner to wagev2;

