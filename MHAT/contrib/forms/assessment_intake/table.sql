CREATE TABLE IF NOT EXISTS form_assessment_intake (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,
dcn varchar(10) default NULL,
location varchar(20) default NULL,
time_in time default NULL,
time_out time default NULL,
referral_source varchar(15) default NULL,
new_client_eval varchar(3) NOT NULL default 'N/A',
readmission varchar(3) NOT NULL default 'N/A',
consultation varchar(3) NOT NULL default 'N/A',
copy_sent_to varchar(25) default NULL,
reason_why longtext,
behavior_led_to longtext,
school_work longtext,
personal_relationships longtext,
fatherc varchar(3) NOT NULL default 'N/A',
motherc varchar(3) NOT NULL default 'N/A',
father_involved longtext,
mother_involved longtext,
number_children tinyint(4),
siblings longtext,
other_relationships longtext,
social_relationships longtext,
current_symptoms longtext,
personal_strengths longtext,
spiritual varchar(15) default NULL,
legal varchar(20) default NULL,
prior_history longtext,
number_admitt tinyint(4),
type_admitt varchar(20),
substance_use longtext,
substance_abuse longtext,
axis1 longtext,
axis2 longtext,
axis3 longtext,
allergies varchar(20),
ax4_prob_support_group varchar(3) NOT NULL default 'N/A',
ax4_prob_soc_env varchar(3) NOT NULL default 'N/A',
ax4_educational_prob varchar(3) NOT NULL default 'N/A',
ax4_occ_prob varchar(3) NOT NULL default 'N/A',
ax4_housing varchar(3) NOT NULL default 'N/A',
ax4_economic varchar(3) NOT NULL default 'N/A',
ax4_access_hc varchar(3) NOT NULL default 'N/A',
ax4_legal varchar(3) NOT NULL default 'N/A',
ax4_other_cb varchar(3) NOT NULL default 'N/A',
ax4_other longtext,
ax5_current varchar(3),
ax5_past varchar(3),
risk_suicide_na varchar(3) NOT NULL default 'N/A',
risk_suicide_nk varchar(3) NOT NULL default 'N/A',
risk_suicide_io varchar(3) NOT NULL default 'N/A',
risk_suicide_plan varchar(3) NOT NULL default 'N/A',
risk_suicide_iwom varchar(3) NOT NULL default 'N/A',
risk_suicide_iwm varchar(3) NOT NULL default 'N/A',
risk_homocide_na varchar(3) NOT NULL default 'N/A',
risk_homocide_nk varchar(3) NOT NULL default 'N/A',
risk_homocide_io varchar(3) NOT NULL default 'N/A',
risk_homocide_plan varchar(3) NOT NULL default 'N/A',
risk_homocide_iwom varchar(3) NOT NULL default 'N/A',
risk_homocide_iwm varchar(3) NOT NULL default 'N/A',
risk_compliance_na varchar(3) NOT NULL default 'N/A',
risk_compliance_fc varchar(3) NOT NULL default 'N/A',
risk_compliance_mc varchar(3) NOT NULL default 'N/A',
risk_compliance_moc varchar(3) NOT NULL default 'N/A',
risk_compliance_var varchar(3) NOT NULL default 'N/A',
risk_compliance_no varchar(3) NOT NULL default 'N/A',
risk_substance_na varchar(3) NOT NULL default 'N/A',
risk_substance_none varchar(3) NOT NULL default 'N/A',
risk_normal_use longtext,
risk_substance_ou varchar(3) NOT NULL default 'N/A',
risk_substance_dp varchar(3) NOT NULL default 'N/A',
risk_substance_ur varchar(3) NOT NULL default 'N/A',
risk_substance_ab varchar(3) NOT NULL default 'N/A',
risk_sexual_na varchar(3) NOT NULL default 'N/A',
risk_sexual_y varchar(3) NOT NULL default 'N/A',
risk_sexual_n varchar(3) NOT NULL default 'N/A',
risk_sexual_ry varchar(3) NOT NULL default 'N/A',
risk_sexual_rn varchar(3) NOT NULL default 'N/A',
risk_sexual_cv varchar(3) NOT NULL default 'N/A',
risk_sexual_cp varchar(3) NOT NULL default 'N/A',
risk_sexual_b varchar(3) NOT NULL default 'N/A',
risk_sexual_nf varchar(3) NOT NULL default 'N/A',
risk_neglect_na varchar(3) NOT NULL default 'N/A',
risk_neglect_y varchar(3) NOT NULL default 'N/A',
risk_neglect_n varchar(3) NOT NULL default 'N/A',
risk_neglect_ry varchar(3) NOT NULL default 'N/A',
risk_neglect_rn varchar(3) NOT NULL default 'N/A',
risk_neglect_cv varchar(3) NOT NULL default 'N/A',
risk_neglect_cp varchar(3) NOT NULL default 'N/A',
risk_neglect_cb varchar(3) NOT NULL default 'N/A',
risk_neglect_cn varchar(3) NOT NULL default 'N/A',
risk_exists_c varchar(3) NOT NULL default 'N/A',
risk_exists_cn varchar(3) NOT NULL default 'N/A',
risk_exists_s varchar(3) NOT NULL default 'N/A',
risk_exists_o varchar(3) NOT NULL default 'N/A',
risk_exists_b varchar(3) NOT NULL default 'N/A',
risk_community longtext,
recommendations_psy_i varchar(3) NOT NULL default 'N/A',
recommendations_psy_f varchar(3) NOT NULL default 'N/A',
recommendations_psy_m varchar(3) NOT NULL default 'N/A',
recommendations_psy_o varchar(3) NOT NULL default 'N/A',
recommendations_psy_notes longtext,
refer_date date default NULL,
parent date default NULL,
supervision_level longtext,
supervision_type longtext,
supervision_res longtext,
supervision_services longtext,
support_ps varchar(3) NOT NULL default 'N/A',
support_cs varchar(3) NOT NULL default 'N/A',
support_sm varchar(3) NOT NULL default 'N/A',
support_a varchar(3) NOT NULL default 'N/A',
support_o varchar(3) NOT NULL default 'N/A',
support_ol longtext,
legal_op varchar(3) NOT NULL default 'N/A',
legal_so varchar(3) NOT NULL default 'N/A',
legal_sa varchar(3) NOT NULL default 'N/A',
legal_ve varchar(3) NOT NULL default 'N/A',
legal_ad varchar(3) NOT NULL default 'N/A',
legal_adl varchar(20) default NULL,
legal_o varchar(3) NOT NULL default 'N/A',
legal_ol longtext,
referrals_pepm longtext,
referrals_mc longtext,
referrals_vt longtext,
referrals_o longtext,
referrals_cu longtext,
referrals_docs longtext,
referrals_or longtext,
PRIMARY KEY (id)
) ENGINE=MyISAM;
