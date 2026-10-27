USE cloudsoftera_feedback;

ALTER TABLE feedback
  MODIFY COLUMN category ENUM('Website / web application','Mobile application','Desktop software','Internal business software','API / integration','Other') NOT NULL DEFAULT 'Other',
  ADD COLUMN questionnaire_json JSON NULL AFTER rating;
